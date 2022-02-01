<?php

namespace Lens\Bundle\LpmBundle;

use Lens\Bundle\LpmBundle\Package\Document;
use Lens\Bundle\LpmBundle\Package\Itec\Assessment;
use Lens\Bundle\LpmBundle\Package\Itec\Exam;
use Lens\Bundle\LpmBundle\Package\Itec\Exercise;
use Lens\Bundle\LpmBundle\Package\Package;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class LpmHttpClient implements HttpClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer,
    ) {
    }

    public function list(): array
    {
        $packages = $this->request('GET', 'list')->toArray();
        if (empty($packages['items'])) {
            return [];
        }

        return $this->denormalizePackages($packages['items']);
    }


    public function documents(): array
    {
        $packages = $this->request('GET', 'list/document')->toArray();
        if (empty($packages['items'])) {
            return [];
        }

        return $this->denormalizePackages($packages['items']);
    }

    public function exams(): array
    {
        $packages = $this->request('GET', 'list/exam')->toArray();
        if (empty($packages['items'])) {
            return [];
        }

        return $this->denormalizePackages($packages['items']);
    }

    public function exercises(): array
    {
        $packages = $this->request('GET', 'list/exercise')->toArray();
        if (empty($packages['items'])) {
            return [];
        }

        return $this->denormalizePackages($packages['items']);
    }

    public function assessments(): array
    {
        $packages = $this->request('GET', 'list/assessment')->toArray();
        if (empty($packages['items'])) {
            return [];
        }

        return $this->denormalizePackages($packages['items']);
    }

    public function download(Package|Uuid|string $package, string $path = null): File
    {
        if ($package instanceof Package) {
            $package = $package->id;
        }

        $response = $this->httpClient->request('GET', 'download/'.$package);
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new RuntimeException('Failed to download lpm package!');
        }

        $handler = $path ? fopen($path, 'wb') : tmpfile();
        foreach ($this->httpClient->stream($response) as $chunk) {
            fwrite($handler, $chunk->getContent());
        }

        fclose($handler);

        return new File($path);
    }

    public function unpack(File|string $file, string $targetPath): void
    {
        $sourcePath = $file;
        if ($sourcePath instanceof File) {
            $sourcePath = $sourcePath->getRealPath();
        }

        $zip = new \ZipArchive();
        if (!$zip->open($sourcePath)) {
            throw new RuntimeException(sprintf(
                'Invalid or unable to open ZIP archive "%s".',
                $sourcePath,
            ));
        }

        $success = $zip->extractTo($targetPath);
        if (!$success) {
            throw new RuntimeException(sprintf(
                'Problems while trying to extract package "%s" to "%s"',
                $sourcePath,
                $targetPath,
            ));
        }

        $zip->close();
    }

    public function delete(Package|Uuid|string $package): bool
    {
        if ($package instanceof Package) {
            $package = $package->id;
        }

        $response = $this->httpClient->request('DELETE', 'delete/'.$package);

        if (Response::HTTP_FORBIDDEN === $response->getStatusCode()) {
            throw new RuntimeException(sprintf(
                'You are not allowed to delete the package "%s"',
                $package,
            ));
        }

        return Response::HTTP_OK === $response->getStatusCode();
    }

    private function denormalizePackages(array $packages): array
    {
        $values = [];
        foreach ($packages as $package) {
           $values[$package['id']] = $this->denormalizePackage($package);
        }

        return $values;
    }

    private function denormalizePackage(array $package): Package
    {
        return $this->serializer->denormalize($package, match($package['type']) {
            'document' => Document::class,
            'exam' => Exam::class,
            'exercise' => Exercise::class,
            'assessment' => Assessment::class,
        });
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->httpClient->request($method, $url, $options);
    }

    public function stream($responses, float $timeout = null): ResponseStreamInterface
    {
        return $this->httpClient->stream($responses, $timeout);
    }

    public function withOptions(array $options)
    {
        return $this->httpClient->withOptions($options);
    }
}
