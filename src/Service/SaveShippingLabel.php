<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Service;

use Omobude\DhlBundle\Exception\DhlDownloadLabelException;
use Psr\Log\LoggerInterface;

class SaveShippingLabel
{
    public function __construct(
        private readonly ?LoggerInterface $logger = null
    ) {}

    /**
     * Saves a base64 encoded shipping label to the filesystem.
     *
     * @param string $base64Label The base64 encoded label content
     * @param string $fileName The filename to save as
     * @param string|null $directory Optional directory path (defaults to system temp)
     * @return string The full file path where the label was saved
     * @throws DhlDownloadLabelException
     */
    public function saveShippingLabel(string $base64Label, string $fileName, ?string $directory = null): string
    {
        try {
            $decodedLabel = base64_decode($base64Label, true);
            if ($decodedLabel === false) {
                throw new DhlDownloadLabelException('Failed to decode the shipping label. Invalid base64 content.');
            }

            $saveDirectory = $directory ?? sys_get_temp_dir();

            // Ensure directory exists
            if (!is_dir($saveDirectory)) {
                if (!mkdir($saveDirectory, 0755, true) && !is_dir($saveDirectory)) {
                    throw new DhlDownloadLabelException(
                        sprintf('Failed to create directory: %s', $saveDirectory)
                    );
                }
            }

            // Sanitize filename to prevent directory traversal
            $sanitizedFileName = basename($fileName);
            $filePath = $saveDirectory . DIRECTORY_SEPARATOR . $sanitizedFileName;

            // Write file
            $bytesWritten = file_put_contents($filePath, $decodedLabel);

            if ($bytesWritten === false) {
                throw new DhlDownloadLabelException(
                    sprintf('Failed to write label to file: %s', $filePath)
                );
            }

            $this->logger?->info('DHL shipping label saved successfully', [
                'file_path' => $filePath,
                'size_bytes' => $bytesWritten,
            ]);

            return $filePath;

        } catch (DhlDownloadLabelException $ex) {
            $this->logger?->error('Failed to save DHL shipping label', [
                'error' => $ex->getMessage(),
                'filename' => $fileName,
            ]);
            throw $ex;
        } catch (\Exception $ex) {
            $this->logger?->error('Unexpected error saving DHL shipping label', [
                'error' => $ex->getMessage(),
                'filename' => $fileName,
            ]);
            throw new DhlDownloadLabelException(
                sprintf('Failed to save shipping label: %s', $ex->getMessage()),
                $ex->getCode(),
                $ex
            );
        }
    }
}
