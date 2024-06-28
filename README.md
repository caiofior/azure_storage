# Access to azure storeabe table and blob with a shared key

This two files allows to access to an azure storage table and blob from a php project.

It requires [Guzzle](https://github.com/guzzle/guzzle).

I made this small project because [official microsoft libraries](https://github.com/Azure/azure-storage-common-php) are deprecated

```php
$azureTable = new \Ftc\driver\AzureStorageTable(
    AZURE_STORAGE_ACCOUNT_TABLE_NAME,
    AZURE_STORAGE_ACCOUNT_TABLE_BASEURL,
    AZURE_STORAGE_ACCOUNT_TABLE_TOKENSAS
);

try {
    $tokenNata = $azureTable->get('file','%24filter='.urlencode('PartitionKey eq \''.$token.'\''));
} catch (\Exception $e) {
    die("Token errato !");
}
```
Example of sorage table access

```php
    $azureBlob = new \Ftc\driver\AzureStorageBlob(
        AZURE_STORAGE_ACCOUNT_FILE_NAME,
        AZURE_STORAGE_ACCOUNT_FILE_BASEURL,
        AZURE_STORAGE_ACCOUNT_FILE_TOKENSAS
    );

   $azureBlob->put(
        'file',
        $_FILES['uploadFile']['name'],
        $_FILES['uploadFile']['type'],
        $_FILES['uploadFile']['size'],
        fopen($_FILES['uploadFile']['tmp_name'],'r')
   );
```
Example of file uploading

(shared_key.jpeg)

How to generate a shared key
