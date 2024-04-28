<?php

namespace Inpi;

use DOMDocument;
use Inpi\Data\Brand;
use Inpi\Data\Dispatch;
use Inpi\Data\Holder;
use Inpi\Data\Process;
use Inpi\Helpers\XMLReader;

class InpiService
{
    const string INPI_MAGAZINE_URL = 'https://revistas.inpi.gov.br/txt/RM{ID}.zip';

    public function getLastMagazineId(): string
    {
        $dom = new DOMDocument();
        @$dom->loadHTMLFile('https://revistas.inpi.gov.br/rpi/');
        $finder = new \DOMXPath($dom);

        return $finder->query("//tr[@class='warning']//td")[0]?->nodeValue;
    }

    public function execute(string|null $magazineId = null): void
    {
        $init = microtime(true);

        if (is_null($magazineId)) {
            $magazineId = $this->getLastMagazineId();
        }

        $magazineZipUrl = str_replace('{ID}', $magazineId, self::INPI_MAGAZINE_URL);
        $path = __DIR__ . '/../tmp/inpi.zip';
        $tmpDir = __DIR__ . '/../tmp/';
        $this->downloadZip($magazineZipUrl, $path);
        $this->extractZip($path, $tmpDir);
        // Remove the zip file, don't need it anymore
        unlink($path);

        $xmlPath = $tmpDir . "RM{$magazineId}.xml";

        $all = $this->processMagazine($xmlPath);

        echo count($all) . " processos processados\n";
        unlink($xmlPath);

        $end = microtime(true);

        echo "Tempo de execução: " . ($end - $init) . " segundos\n";
    }

    /**
     * @param string $path
     * @return array<Process>
     */
    private function processMagazine(string $path): array
    {
        $xml = new XMLReader($path);
        $all = [];

        while($xml->read()) {
            if ($xml->isElement('revista')) {
                break; // Found the revista element
            }
        }

        while ($xml->read() && !$xml->isEndElement('revista')) {

            if ($xml->isElement('processo')) {
                $process = $xml->getNode();

                $dispatches = [];
                foreach ($process->xpath('despachos/despacho') as $dispatch) {
                    $dispatches[] = new Dispatch(
                        $dispatch->get('codigo'),
                        $dispatch->get('nome'),
                    );
                }

                $holders = [];
                foreach ($process->xpath('titulares/titular') as $holder) {
                    $holders[] = new Holder(
                        $holder->get('nome-razao-social'),
                        $holder->get('pais'),
                        $holder->get('uf'),
                    );
                }

                $brand = null;
                if ($process->has('marca')) {
                    $brandNode = $process->first('marca');
                    $brand = new Brand(
                        $brandNode->has('nome') ? $brandNode->first('nome')->value() : null,
                        $brandNode->get('apresentacao'),
                        $brandNode->get('natureza'),
                    );
                }

                $status = null;
                if ($process->has('lista-classe-nice/classe-nice/status')) {
                    $status = $process->first('lista-classe-nice/classe-nice/status')->value();
                }

                $all[] = new Process(
                    $process->get('numero'),
                    $process->get('data-deposito'),
                    $dispatches,
                    $holders,
                    $brand,
                    $status
                );

                echo "Processo {$process->get('numero')} processado\n";
            }

        }

        $xml->close();

        return $all;
    }

    /**
     * Download the magazine zip file in $outDir
     *
     * @param string $url
     * @param string $outDir
     * @return void
     */
    private function downloadZip(string $url, string $outDir): void
    {
        $data = file_get_contents($url);

        $temp = fopen($outDir, 'w');
        fwrite($temp, $data);
        fseek($temp, 0);
        fclose($temp);
    }

    private function extractZip(string $path, string $outDir): void
    {
        $zip = new \ZipArchive;

        if ($zip->open($path) === TRUE) {
            $zip->extractTo($outDir);
            $zip->close();
        } else {
            die('Error');
        }
    }
}