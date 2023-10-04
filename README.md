# Class servant de librairie des calls API au S3 d'Amazon pour la gestion des buckets et keys

> Utilisation en local de [MinIO](https://min.io/docs/minio/linux/index.html) qui est l'Open Source du S3

Requiert PHP >=7.4

## Utilisation de la librairie

### Initialisation
```php
// Initialise le Client S3 avec les credentials, l'endpoint de base et la région du server AWS
$oS3 = new \DisDev\S3\S3('accessKey', 'secretKey', 'endpoint', 'region');

// Un 5ème argument qui est le nom du bucket si l'instance comporte un seul bucket, à ne pas réutiliser à chaque appel de méthode ;)
$oS3 = new \DisDev\S3\S3('accessKey', 'secretKey', 'endpoint', 'region', 'bucketName');
```

## Méthodes API
> L'argument `$bucketName` n'est pas obligatoire si `$bucketName` a été renseigné dans le constructeur

```php
// Si un bucket existe
public function doesBucketExist(string $bucketName = ''): bool;

// Si un object existe par sa clef et bucket
public function doesObjectExist(string $keyName, string $bucketName = ''): bool;

// Créé un bucket (si le bucket est déjà créé, ne fait rien et continue le process)
public function createBucket(string $bucketName = ''): void

// Créé ou remplace un object S3 d'un contenu dans un string
public function putObject(string $content, string $keyName, string $contentType, string $bucketName = ''): void

// Créé ou remplace un object S3 d'un contenu d'un fichier
public function putFile(string $filePath, string $keyName, string $contentType, string $bucketName = ''): void

// Retourne une instance `\Aws\Result` selon la clef et le bucket associés
public function getObject(string $key, string $bucketName = ''): \Aws\Result

// Retourne le contenu d'un fichier du S3 selon la clef et le bucket
public function getObjectContent(string $key, string $bucketName = ''): string

// Essaie de supprimer un bucket (si le bucket n'existe pas, ne fait rien et continue le process)
public function deleteBucket(string $bucketName = ''): bool

// Essaie de supprimer un object (si le bucket n'existe pas, ne fait rien et continue le process)
public function deleteObject(string $keyName, string $bucketName = ''): bool

// @return array<string, string[]> Retourne les buckets/objects créés (bucketName -> array de noms de clef)
public function getObjects(): array;
```

## Installation pour le développement

1. Copier [.env.example](.env.example) vers `.env`

2. Lancer les containers docker (`docker compose up -d`), deux containers vont être lancés :
    - `s3-7.4` en php7.4
    - `s3-8.1` en php8.1

3. Lancer `docker compose exec s3-7.4|s3-8.1 bash` pour accéder au PHP.

4. Aller sur `http://localhost:9090` et se connecter avec le user/password dans le [docker-compose.yml](docker-compose.yml)

5. Aller dans `Access Keys`, générer les clefs et les mettre dans le `.env`

6. Aller dans `Settings` et mettez une valeur dans `Server Location` (`local-dev` par ex.) puis dans le `.env`

7. Relancer les containers pour mettre à jour le `.env` et vous êtes go to go !