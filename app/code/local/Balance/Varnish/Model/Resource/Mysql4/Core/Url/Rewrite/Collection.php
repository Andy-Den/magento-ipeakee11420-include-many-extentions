<?php
if (class_exists('Enterprise_UrlRewrite_Model_Resource_Url_Rewrite_Collection', false)) {
    class Balance_Varnish_Model_Resource_Mysql4_Core_Url_Rewrite_Collection
        extends Enterprise_UrlRewrite_Model_Resource_Url_Rewrite_Collection
        //    extends Mage_Core_Model_Mysql4_Url_Rewrite_Collection
    {
        /**
         * Filter collection by category id
         *
         * @param int $categoryId
         *
         * @return Balance_Varnish_Model_Resource_Mysql4_Core_Url_Rewrite_Collection
         */
        public function filterAllByCategoryId($categoryId)
        {
            $this->getSelect()
                ->where('target_path = ?', "catalog/category/view/id/{$categoryId}");

            return $this;
        }
    }

} else {

    class Balance_Varnish_Model_Resource_Mysql4_Core_Url_Rewrite_Collection
        extends Mage_Core_Model_Mysql4_Url_Rewrite_Collection
    {
        /**
         * Filter collection by category id
         *
         * @param int $categoryId
         *
         * @return Balance_Varnish_Model_Resource_Mysql4_Core_Url_Rewrite_Collection
         */
        public function filterAllByCategoryId($categoryId)
        {
            $this->getSelect()
                ->where('id_path = ?', "category/{$categoryId}");

            return $this;
        }
    }

}