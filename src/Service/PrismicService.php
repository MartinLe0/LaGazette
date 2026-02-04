<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for interacting with the Prismic CMS API.
 */
class PrismicService
{
    private string $apiEndpoint;
    private string $apiToken;

    /**
     * @param HttpClientInterface $httpClient The HTTP client
     * @param TagAwareCacheInterface $cache The cache interface
     * @param LoggerInterface $logger The logger interface
     * @param string $prismicRepoName The Prismic repository name (from parameters)
     * @param string $prismicToken The Prismic API token (from parameters)
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private TagAwareCacheInterface $cache,
        private LoggerInterface $logger,
        string $prismicRepoName,
        string $prismicToken
    ) {
        $this->apiEndpoint = sprintf('https://%s.prismic.io/api/v2', $prismicRepoName);
        $this->apiToken = $prismicToken;
    }

    /**
     * Get the master ref from Prismic API
     */
    private function getMasterRef(): string
    {
        return $this->cache->get('prismic_master_ref', function (ItemInterface $item) {
            $item->expiresAfter(300); // Check for new ref every 5 minutes

            try {
                $response = $this->httpClient->request('GET', $this->apiEndpoint, [
                    'query' => ['access_token' => $this->apiToken],
                    'timeout' => 3
                ]);

                $data = $response->toArray();
                foreach ($data['refs'] as $ref) {
                    if ($ref['isMasterRef']) {
                        return $ref['ref'];
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error('Prismic Master Ref Error: ' . $e->getMessage());
                // Return a previously cached ref if possible or fail
                throw $e;
            }

            throw new \RuntimeException('Master ref not found in Prismic API');
        });
    }

    /**
     * Fetch a document by its slug/type and filter its data
     */
    public function getDocument(string $type, string $slug): ?array
    {
        $cacheKey = sprintf('prismic_doc_%s_%s', $type, $slug);

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($type, $slug) {
                // Extended TTL for better availability during API downtime
                $item->expiresAfter(86400); // 24 hours
                $item->tag(['prismic_content']);

                $ref = $this->getMasterRef();
                $query = sprintf('[[at(my.%s.uid, "%s")]]', $type, $slug);

                $response = $this->httpClient->request('GET', $this->apiEndpoint . '/documents/search', [
                    'query' => [
                        'ref' => $ref,
                        'q' => $query,
                        'access_token' => $this->apiToken
                    ],
                    'timeout' => 5,
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new \RuntimeException('Prismic API returned ' . $response->getStatusCode());
                }

                $results = $response->toArray()['results'] ?? [];
                if (empty($results)) {
                    return null;
                }

                return $this->filterData($results[0]);
            });
        } catch (\Exception $e) {
            $this->logger->error('Prismic Integration Error: ' . $e->getMessage(), [
                'type' => $type,
                'slug' => $slug
            ]);

            return null;
        }
    }

    /**
     * Fetch all articles from Prismic, optionally filtered by category
     */
    public function getArticles(int $page = 1, int $pageSize = 12, ?string $category = null): array
    {
        $cacheKey = sprintf('prismic_articles_p%d_s%d_c%s', $page, $pageSize, $category ?? 'all');

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($page, $pageSize, $category) {
                $item->expiresAfter(3600);
                $item->tag(['prismic_content']);

                $ref = $this->getMasterRef();
                $queries = ['[[at(document.type, "article")]]'];

                if ($category) {
                    $queries[] = sprintf('[[at(my.article.category, "%s")]]', $category);
                }

                $response = $this->httpClient->request('GET', $this->apiEndpoint . '/documents/search', [
                    'query' => [
                        'ref' => $ref,
                        'q' => '[' . implode('', $queries) . ']',
                        'access_token' => $this->apiToken,
                        'page' => $page,
                        'pageSize' => $pageSize,
                        'orderings' => '[document.last_publication_date desc]'
                    ],
                    'timeout' => 5,
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new \RuntimeException('Prismic API returned ' . $response->getStatusCode());
                }

                $data = $response->toArray();

                return [
                    'results' => array_map([$this, 'filterData'], $data['results']),
                    'total_pages' => $data['total_pages'],
                    'total_results_size' => $data['total_results_size'],
                ];
            });
        } catch (\Exception $e) {
            $this->logger->error('Prismic Articles Fetch Error: ' . $e->getMessage());
            return ['results' => [], 'total_pages' => 0, 'total_results_size' => 0];
        }
    }

    /**
     * Filter sensitive or unnecessary metadata from Prismic response
     */
    private function filterData(array $document): array
    {
        // Keep only what's needed for the frontend
        return [
            'id' => $document['id'],
            'uid' => $document['uid'],
            'type' => $document['type'],
            'data' => $document['data'], // This contains the actual CMS content
            'slug' => $document['slugs'][0] ?? null,
            'last_publication_date' => $document['last_publication_date'],
        ];
    }

    /**
     * Clear Prismic cache
     */
    public function clearCache(): void
    {
        $this->cache->invalidateTags(['prismic_content']);
    }
}
