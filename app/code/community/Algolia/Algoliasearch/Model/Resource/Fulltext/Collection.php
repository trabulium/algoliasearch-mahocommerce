<?php

class Algolia_Algoliasearch_Model_Resource_Fulltext_Collection extends Mage_CatalogSearch_Model_Resource_Fulltext_Collection
{
    /**
     * Get found products ids
     *
     * @return array|Algolia_Algoliasearch_Model_Resource_Fulltext_Collection
     */
    public function getFoundIds()
    {
        if (!$this->_helper()->isX3Version()) {
            // For older versions, search is handled in addSearchFilter
            // We need to check if we already have Algolia data
            $query = $this->_getQuery();
            if (!empty($query)) {
                $data = $this->getAlgoliaData($query);
                if ($data !== false) {
                    return array_keys($data);
                }
            }
            return parent::getFoundIds();
        }
        
        $query = $this->_getQuery();
        if (is_null($this->_foundData) && !empty($query) && $query instanceof Mage_CatalogSearch_Model_Query) {
            $data = $this->getAlgoliaData($query->getQueryText());
            if (false === $data) {
                return parent::getFoundIds();
            }
            
            $this->_foundData = $data;
        }
        
        return parent::getFoundIds();
    }
    
    /**
     * @return Algolia_Algoliasearch_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('algoliasearch');
    }

    /**
     * @param string $query
     *
     * @return array|bool
     */
    protected function getAlgoliaData($query)
    {
        /** @var Algolia_Algoliasearch_Helper_Config $config */
        $config  = Mage::helper('algoliasearch/config');
        $storeId = Mage::app()->getStore()->getId();

        if (!$config->getApplicationID() || !$config->getAPIKey() || $config->isEnabledFrontEnd($storeId) === false) {
            return false;
        }

        $data = array();

        if ($config->isInstantEnabled($storeId) === false || $config->makeSeoRequest($storeId)) {
            $algolia_query = $query !== '__empty__' ? $query : '';

            try {
                $data = $this->_helper()->getSearchResult($algolia_query, $storeId);
            } catch (\Exception $e) {
                /** @var Algolia_Algoliasearch_Helper_Logger $logger */
                $logger = Mage::helper('algoliasearch/logger');

                $logger->log($e->getMessage(), true);
                $logger->log($e->getTraceAsString(), true);

                return false;
            }
        }

        return $data;
    }

    /**
     * Get query text
     * 
     * @return string|null
     */
    protected function _getQuery()
    {
        $query = Mage::helper('catalogsearch')->getQuery();
        if ($query) {
            return $query->getQueryText();
        }
        return null;
    }

    /**
     * @param string $query
     *
     * @return Algolia_Algoliasearch_Model_Resource_Fulltext_Collection
     */
    public function addSearchFilter($query)
    {
        if ($this->_helper()->isX3Version()) {
            return $this;
        }

        $data = $this->getAlgoliaData($query);
        if (false === $data) {
            return parent::addSearchFilter($query);
        }
        $sortedIds = array_reverse(array_keys($data));

        if (!empty($sortedIds)) {
            $this->getSelect()->columns(array(
                'relevance' => new Zend_Db_Expr("FIND_IN_SET(e.entity_id, '" . implode(',', $sortedIds) . "')"),
            ));
            $this->getSelect()->where('e.entity_id IN (?)', $sortedIds);
        } else {
            // No results, return empty collection
            $this->getSelect()->where('1=0');
        }

        return $this;
    }
}
