<?php
namespace Ftc\driver;
use http\Encoding\Stream;
use GuzzleHttp;

/**
 * Access to azure blob storage
 */
class AzureStorageBlob {
    private $storageAccountName = null;
    private $tokenSAS = null;

    private $baseUrl = null;

    /**
     * Predefined headers
     * @return string[]
     */
    private function getHeaders() {
        return [
            'x-ms-blob-type'=> 'BlockBlob'
        ];
     }

    /**
     * Initalize class
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
     * List available files
     * @param string $tableName
     * @param string $query
     * @return \$1|false|\SimpleXMLElement
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function list(string $tableName,string $query='' ) {
        $queryURL = "$this->baseUrl$this->storageAccountName/$tableName?restype=container&comp=list&$this->tokenSAS&".$query;

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

        if ($http_status == 200) {
            return simplexml_load_string($result);
        } else {
            rewind($streamVerboseHandle);
            $verboseLog = stream_get_contents($streamVerboseHandle);
            throw new \Exception(
                nl2br($verboseLog)
            );
        }
    }

    /**
     * Gets a specific file
     * @param string $tableName
     * @param string $name
     * @return array
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $tableName,string $name ) {
        $name = urlencode($name);
        $queryURL = "$this->baseUrl$this->storageAccountName/$tableName/$name?$this->tokenSAS";

        $streamVerboseHandle = fopen('php://temp', 'w+');
        $client = new GuzzleHttp\Client();
        $res = $client->get(
            $queryURL,
            [
                'debug'=>$streamVerboseHandle,
                'http_errors' => false
            ]
        );
        $http_status = $res->getStatusCode();
        $result = $res->getBody();
        $headers = $res->getHeaders();
        if (in_array($http_status,array(200,201,202))) {
            return ['result'=>$result,'headers'=>$headers];
        } else {
            rewind($streamVerboseHandle);
            $verboseLog = stream_get_contents($streamVerboseHandle);
            throw new \Exception(
                nl2br($verboseLog)
            );
        }
    }

    /**
     * Replaces a file
     * @param string $tableName
     * @param string $name
     * @param string $type
     * @param int $size
     * @param $stream
     * @return \$1|false|\SimpleXMLElement
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function put(string $tableName,string $name, string $type, int $size, $stream) {
        $name = urlencode($name);
        $queryURL = "$this->baseUrl$this->storageAccountName/$tableName/$name?$this->tokenSAS";


        $headers = $this->getHeaders();
        $headers['Content-Type']= $type;
        $headers['Content-Length']=$size;

        $streamVerboseHandle = fopen('php://temp', 'w+');
        $resource = \GuzzleHttp\Psr7\Utils::streamFor($stream);

        $client = new GuzzleHttp\Client();
        $res = $client->put(
            $queryURL,
            [
                'body' => $resource,
                'headers' => $headers,
                'debug'=>$streamVerboseHandle,
                'http_errors' => false
            ]
        );
        $http_status = $res->getStatusCode();
        $result = $res->getBody();
        if (in_array($http_status,array(200,201,202))) {
            return simplexml_load_string($result);
        } else {
            rewind($streamVerboseHandle);
            $verboseLog = stream_get_contents($streamVerboseHandle);
            throw new \Exception(
                nl2br($verboseLog)
            );
        }

    }

    /**
     * dDeleets a file
     * @param string $tableName
     * @param string $name
     * @return \$1|false|\SimpleXMLElement
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function delete(string $tableName,string $name ) {
        $name = urlencode($name);
        $queryURL = "$this->baseUrl$this->storageAccountName/$tableName/$name?$this->tokenSAS";


        $streamVerboseHandle = fopen('php://temp', 'w+');


        $client = new GuzzleHttp\Client();
        $res = $client->delete(
            $queryURL,
            [
                'headers' => $this->getHeaders(),
                'debug'=>$streamVerboseHandle,
                'http_errors' => false
            ]
        );
        $http_status = $res->getStatusCode();
        $result = $res->getBody();
        if (in_array($http_status,array(200,201,202))) {
            return simplexml_load_string($result);
        } elseif ($http_status == 404) {
            throw new \Exception('File '.$name.' does not exists');
        } else {
            rewind($streamVerboseHandle);
            $verboseLog = stream_get_contents($streamVerboseHandle);
            throw new \Exception(
                nl2br($verboseLog)
            );
        }
    }
}
