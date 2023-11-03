<?php

/**
 * The domain provider mapper.
 */

namespace OCA\RDMesh\Federation;

use Exception;
use OCA\RDMesh\AppInfo\RDMesh;
use OCA\RDMesh\Db\Schema;
use OCA\RDMesh\Service\NotFoundException;
use OCP\AppFramework\Db\Mapper;
use OCP\IDb;
use OCP\ILogger;

class DomainProviderMapper extends Mapper
{
    private ILogger $logger;

    public function __construct(IDb $dbConnection)
    {
        parent::__construct($dbConnection, Schema::Table_DomainProviders, DomainProvider::class);
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Returns all domain domain providers
     * 
     * @return array
     * @throws NotFoundException
     */
    public function allDomainProviders(): array
    {
        try {
            $qb = $this->db->getQueryBuilder();
            $qb->select('*')
                ->from(Schema::Table_DomainProviders, 'dp');
            return $this->getDomainProviders($qb->execute()->fetchAllAssociative());
        }catch(Exception $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => RDMesh::APP_NAME]);
            throw new NotFoundException('Error retrieving all domain providers');
        }
    }

    /**
     * Builds and returns a new DomainProvider object from the specified associative array.
     * @param array $associativeArray
     * @return DomainProvider
     */
    private function getDomainProvider(array $associativeArray): DomainProvider
    {
        if (isset($associativeArray) && count($associativeArray) > 0) {
            $domainProvider = new DomainProvider();
            $domainProvider->setId($associativeArray['id']);
            $domainProvider->setDomain($associativeArray[Schema::DomainProvider_domain]);
            return $domainProvider;
        }
        $this->logger->error('Unable to create a new DomainProvider from associative array: ' . print_r($associativeArray, true), ['app' => RDMesh::APP_NAME]);
        return null;
    }

    /**
     * Builds and returns an array of new DomainProvider objects from the specified associatives array.
     * @param array $associativeArrays
     * @return array
     */
    private function getDomainProviders(array $associativeArrays): array
    {
        $domainProviders = [];
        if (isset($associativeArrays) && count($associativeArrays) > 0) {
            foreach ($associativeArrays as $associativeArray) {
                array_push($domainProviders, $this->getDomainProvider($associativeArray));
            }
        }
        return $domainProviders;
    }
}
