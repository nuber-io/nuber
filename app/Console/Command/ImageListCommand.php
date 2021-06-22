<?php
declare(strict_types = 1);
namespace App\Console\Command;

use Origin\HttpClient\Http;
use Origin\Console\Command\Command;

class ImageListCommand extends Command
{
    protected $name = 'image:list';
    protected $description = 'Downloads a full list of the remote images';

    const URL = 'https://uk.images.linuxcontainers.org/streams/v1/images.json';

    protected function initialize(): void
    {
    }
 
    protected function execute(): void
    {
        $response = (new Http())->get(self::URL);
        
        $this->io->status($response->ok() ? 'ok' : 'error', 'Image list downloaded');

        if (! $response->ok()) {
            $this->abort();
        }

        // save for debugging
        file_put_contents(tmp_path('images.json'), json_encode($response->json(), JSON_PRETTY_PRINT));

        $out = [];
        foreach ($response->json()['products'] as $image => $data) {
            $imageData = [
                'name' => "{$data['os']} {$data['release_title']} {$data['arch']}",
                'alias' => str_replace(':', '/', $image),
                'arch' => $data['arch'],
                'variant' => $data['variant'],
                'containerFingerprint' => null,
                'virtualMachineFingerprint' => null
            ];

            ksort($data['versions']);
            $latest = array_key_last($data['versions']);
            $imageData['containerFingerprint'] = $data['versions'][$latest]['items']['lxd.tar.xz']['combined_squashfs_sha256'];
            if (! empty($data['versions'][$latest]['items']['lxd.tar.xz']['combined_disk-kvm-img_sha256'])) {
                $imageData['virtualMachineFingerprint'] = $data['versions'][$latest]['items']['lxd.tar.xz']['combined_disk-kvm-img_sha256'];
            }
            $out[] = $imageData;
        }

        if (! (bool) file_put_contents(config_path('images.json'), json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
            $this->throwError('Error writing data  to ' . config_path('images.json'));
        }
    }
}
