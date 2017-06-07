<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

class LS_QBO_Account
{
    public $wpdb;
    public $tableName = 'linksync_qbo_accounts';
    public $charsetCollate = null;
    public $tableVersion = '1.0';

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->tableName = $wpdb->prefix.$this->tableName;
        $this->charsetCollate = $wpdb->get_charset_collate();
    }

    public function getAll()
    {
        return $this->wpdb->get_results("SELECT * FROM $this->tableName ", ARRAY_A);
    }

    public function getById($id)
    {
        if(empty($id)){
            return 'empty_id_being_passed';
        }

        $prepareQuery = $this->wpdb->prepare("SELECT * FROM $this->tableName WHERE Id = %s ", $id);
        return $this->wpdb->get_row($prepareQuery, ARRAY_A);
    }

    public function getByClassification($classification)
    {
        if(empty($classification)){
           return $this->getAll();
        }

        $prepareQuery = $this->wpdb->prepare("SELECT * FROM $this->tableName WHERE classification = %s ", $classification);
        return $this->wpdb->get_results($prepareQuery, ARRAY_A);
    }

    public function getExpenses()
    {
        return $this->getByClassification('Expense');
    }

    public function getRevenues()
    {
        return $this->getByClassification('Revenue');
    }

    public function getAssets()
    {
        return $this->getByClassification('Asset');
    }

    public function getEquities()
    {
        return $this->getByClassification('Equity');
    }

    public function getLiabilities()
    {
        return $this->getByClassification('Liability');
    }

    public function batchInsertUpdate($columnsAndValuesArray)
    {
        $response = array();
        foreach ($columnsAndValuesArray as $account) {
            $response[] = $this->insertAccount($account);
        }
        return $response;
    }

    public function insertAccount($columnsAndValues)
    {
        $accountData = $this->getById($columnsAndValues['id']);
        if (empty($accountData)) {
            return $this->wpdb->insert($this->tableName, $columnsAndValues);
        }

        $id = $columnsAndValues['id'];
        unset($columnsAndValues['id']);
        return $this->wpdb->update($this->tableName, $columnsAndValues, array('id' => $id));
    }

    /**
     * Create linksync Accounts Table
     */
    public function createTable()
    {
        $sql = "CREATE TABLE  IF NOT EXISTS $this->tableName (
                    `id` BIGINT NOT NULL , 
                    `name` VARCHAR(60) DEFAULT NULL , 
                    `subAccount` VARCHAR(250) DEFAULT NULL , 
                    `Description` TEXT DEFAULT NULL , 
                    `fullyQualifiedName` VARCHAR(250) DEFAULT NULL , 
                    `active` VARCHAR(5) DEFAULT NULL , 
                    `classification` VARCHAR(250) DEFAULT NULL , 
                    `AccountType` VARCHAR(250) DEFAULT NULL , 
                    `AccountSubType` VARCHAR(250) DEFAULT NULL , 
                    `CurrentBalance` VARCHAR(250) DEFAULT NULL , 
                    `CurrentBalanceWithSubAccounts` VARCHAR(250) DEFAULT NULL , 
                    `CurrencyRef` TEXT DEFAULT NULL , 
                    `sparse` VARCHAR(5) DEFAULT NULL , 
                    `SyncToken` VARCHAR(250) DEFAULT NULL , 
                    UNIQUE `id` (`id`)
                ) $this->charsetCollate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql );

        add_option('linksync_accounts_table_version', $this->tableVersion);
    }

    /**
     * Table changes or udpates will be added here
     */
    public function updateTable()
    {
        $currentTableVersion = get_option('linksync_accounts_table_version', '1.0');

    }

    public function tableUpgrade()
    {
        $this->createTable();
        $this->updateTable();
    }

    public function dropTable()
    {
        $this->wpdb->query("DROP TABLE IF EXISTS $this->tableName ");
    }
    
}