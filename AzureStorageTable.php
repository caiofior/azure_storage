<?php
namespace Ftc\driver;
use GuzzleHttp;

/**
 * Access to azure storage table
 */
class AzureStorageTable {
    private $storageAccountName = null;
    private $tokenSAS = null;

    private $baseUrl = null;

    /**
     * Initializes the class
     * @param string $storageAccountName
     * @param string $baseUrl
     * @param string $tokenSAS
     */
    public function __construct(
        string $storageAccountName,
        string $baseUrl,
        string $tokenSAS
    ) {
        $this->storageAccountName = $storageAccountName;
        $this->baseUrl = $baseUrl;
        $this->tokenSAS = $tokenSAS;
    }

    /**
     * Gets data from the table
     * @param string $tableName
     * @param string $query
     * @return \$1|false|\SimpleXMLElement
     * @throws \Exception
     */
    public function get(string $tableName,string $query ) {
        $queryURL = "$this->baseUrl$this->storageAccountName/$tableName?$this->tokenSAS&".$query;
        $streamVerboseHandle = fopen('php://temp', 'w+');
        $client = new GuzzleHttp\Client();
        $res = $client->get(
            $queryURL,
            [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'debug'=>$streamVerboseHandle,
                'http_errors' => false
            ]
        );
        $http_status = $res->getStatusCode();
        $result = $res->getBody();

        if (in_array($http_status,array(200,201,202))) {
            return json_decode($result);
        } else {

            rewind($streamVerboseHandle);
            $verboseLog = stream_get_contents($streamVerboseHandle);

            throw new \Exception(
                nl2br($verboseLog)
            );
        }
    }

    /**
     * Insert a new value in a table, trows exception if it is present
     * @param string $tableName
     * @param \stdClass $object
     * @return true
     * @throws \Exception
     */
    public function post(string $tableName,\stdClass $object ) {
        $queryURL = "$this->baseUrl$this->storageAccountName/$tableName?$this->tokenSAS";

        $object->PartitionKey = (string)($object->PartitionKey??'');
        $object->RowKey = (string)($object->RowKey??'');
        $streamVerboseHandle = fopen('php://temp', 'w+');

        $client = new GuzzleHttp\Client();
        $res = $client->post(
            $queryURL,
            [
                'body' => json_encode($object),
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'debug'=>$streamVerboseHandle,
                'http_errors' => false
            ]
        );
        $http_status = $res->getStatusCode();

        if (in_array($http_status,array(200,201,202))) {
            return true;
        } elseif($http_status == 409) {
            throw new \Exception('Record already present');
        } else {
            rewind($streamVerboseHandle);
            $verboseLog = stream_get_contents($streamVerboseHandle);
            throw new \Exception(
                $queryURL.PHP_EOL.
                json_encode($object).PHP_EOL.
                $verboseLog
            );
        }
    }

    public function merge(string $tableName,\stdClass $object ) {
        $object->PartitionKey = (string)($object->PartitionKey??'');
        $object->RowKey = (string)($object->RowKey??'');
        $queryURL = "$this->baseUrl$this->storageAccountName/$tableName(PartitionKey='$object->PartitionKey',RowKey='$object->RowKey')?$this->tokenSAS";

        unset($object->PartitionKey);
        unset($object->RowKey);

        $streamVerboseHandle = fopen('php://temp', 'w+');

        $client = new GuzzleHttp\Client();
        $res = $client->request(
            'MERGE',
            $queryURL,
            [
                'body' => json_encode($object),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'debug'=>$streamVerboseHandle,
                'http_errors' => false
            ]
        );
        $http_status = $res->getStatusCode();

        if (in_array($http_status,array(204))) {
            return true;
        } else {
            rewind($streamVerboseHandle);
            $verboseLog = stream_get_contents($streamVerboseHandle);
            throw new \Exception(
                $queryURL.PHP_EOL.
                json_encode($object).PHP_EOL.
                $verboseLog
            );
        }

    }

    /**
     * Replaces a value in a table
     * @param string $tableName
     * @param \stdClass $object
     * @return true
     * @throws \Exception
     */
    public function put(string $tableName,\stdClass $object ) {
        $object->PartitionKey = (string)($object->PartitionKey??'');
        $object->RowKey = (string)($object->RowKey??'');
        $queryURL = "$this->baseUrl$this->storageAccountName/$tableName(PartitionKey='$object->PartitionKey',RowKey='$object->RowKey')?$this->tokenSAS";

        unset($object->PartitionKey);
        unset($object->RowKey);
        $streamVerboseHandle = fopen('php://temp', 'w+');

        $client = new GuzzleHttp\Client();
        $res = $client->put(
            $queryURL,
            [
                'body' => json_encode($object),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'debug'=>$streamVerboseHandle,
                'http_errors' => false
            ]
        );
        $http_status = $res->getStatusCode();

        if (in_array($http_status,array(204))) {
            return true;
        } else {
            rewind($streamVerboseHandle);
            $verboseLog = stream_get_contents($streamVerboseHandle);
            throw new \Exception(
                $queryURL.PHP_EOL.
                json_encode($object).PHP_EOL.
                $verboseLog
            );
        }
    }

    /**
     * Deletes a record
     * @param string $tableName
     * @param \stdClass $object
     * @return bool
     */
    public function delete(string $tableName,\stdClass $object ) {
        $object->PartitionKey = (string)($object->PartitionKey??'');
        $object->RowKey = (string)($object->RowKey??'');
        $queryURL = "$this->baseUrl$this->storageAccountName/$tableName(PartitionKey='$object->PartitionKey',RowKey='$object->RowKey')?$this->tokenSAS";

        $streamVerboseHandle = fopen('php://temp', 'w+');

        $client = new GuzzleHttp\Client();
        $res = $client->delete(
            $queryURL,
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'If-Match' => '*'
                ],
                'debug'=>$streamVerboseHandle,
                'http_errors' => false
            ]
        );
        $http_status = $res->getStatusCode();

        if (in_array($http_status,array(204))) {
            return true;
        } else {
            rewind($streamVerboseHandle);
            $verboseLog = stream_get_contents($streamVerboseHandle);
            throw new \Exception(
                $queryURL.PHP_EOL.
                $verboseLog
            );
        }
    }
}
