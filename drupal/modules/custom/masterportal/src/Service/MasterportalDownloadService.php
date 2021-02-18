<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Url;
use Drupal\masterportal\Entity\MasterportalInstanceInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ZipArchive;

/**
 * Interface MasterportalDownloadServiceInterface.
 *
 * @package Drupal\masterportal\Service
 */
class MasterportalDownloadService implements MasterportalDownloadServiceInterface {

  /**
   * Drupal's file system service.
   *
   * @var FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The currently processed request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The Masterportal rendering service.
   *
   * @var MasterportalInterface
   */
  protected $renderer;

  /**
   * MasterportalDownloadService constructor.
   *
   * @param FileSystemInterface $file_system
   *   Drupal's file system service.
   * @param RequestStack $request_stack
   *   The request stack.
   * @param MasterportalInterface $masterportal_renderer
   *   The Masterportal rendering service.
   */
  public function __construct(
    FileSystemInterface $file_system,
    RequestStack $request_stack,
    MasterportalInterface $masterportal_renderer
  ) {
    $this->fileSystem = $file_system;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->renderer = $masterportal_renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function createZip(MasterportalInstanceInterface $masterportal_instance) {
    // Copy the library.
    $tmpPath = $this->copyLibrary();

    // Unlink unnecessary files (only used by the config ui).
    $this->fileSystem->unlink(sprintf('%s/css/masterportalAspectRatios.css', $tmpPath));
    $this->fileSystem->unlink(sprintf('%s/css/style-glyphicons.css', $tmpPath));

    // Create dynamic files.
    $dynamicFiles = [
      ['index.html', 'BasicSettings.html_structure', 'text/html; charset=utf-8', NUll],
      ['config.js', 'BasicSettings.js', 'application/javascript; charset=utf-8', 'generateJavascriptSettingsObject'],
      ['config.json', 'JSON', 'application/json; charset=utf-8', 'generateJsonSettingsObject'],
      ['layerdefinitions.json', 'Layerconfiguration', 'application/json; charset=utf-8', 'enrichLayerDefinitions'],
      ['services.json', 'BasicSettings.service_definitions', 'application/json; charset=utf-8', 'generateServicesJson'],
      ['layerstyles.json', 'LayerStyles', 'application/json; charset=utf-8', 'generateLayerStyles'],
      // TODO - refactor to use plugin
      ['contributions.json', 'JSON', 'application/json; charset=utf-8', 'renderLayer'],
    ];
    foreach ($dynamicFiles as $file) {
      $this->createDynamicFile(
        $tmpPath,
        $file[0],
        $masterportal_instance,
        $file[1],
        $file[2],
        $file[3]
      );
    }

    // Create the ZIP.
    $zip = $this->createZipFile($tmpPath);

    // Create the response and attach the file.
    $response = new BinaryFileResponse($zip);
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'masterportal.zip');
    $response->deleteFileAfterSend(TRUE);

    // Remove the temporary Masterportal directory.
    file_unmanaged_delete_recursive($tmpPath);

    return $response;
  }

  /**
   * Copies all necessary files of the Masterportal to a temporary location.
   *
   * @param null $src
   *   Only used internally.
   * @param null $dst
   *   Only used internally.
   *
   * @return string
   *   The temporary path of the copy.
   */
  protected function copyLibrary($src = NULL, $dst = NULL) {
    if (empty($src)) {
      $src = sprintf(
        '%s/libraries/masterportal',
        drupal_get_path('module', 'masterportal')
      );
    }
    if (empty($dst)) {
      $tmpPath = sprintf('%s/masterportal', file_directory_temp());
      $this->fileSystem->mkdir($tmpPath);
      $dst = $this->fileSystem->realpath($tmpPath);
    }
    if (!is_dir($dst)) {
      $this->fileSystem->mkdir($dst);
    }
    $dir = opendir($src);
    while ((FALSE !== ($file = readdir($dir)))) {
      if (in_array($file, ['.', '..'])) {
        continue;
      }
      elseif (is_dir($current = sprintf('%s/%s', $src, $file))) {
        $this->copyLibrary($current, sprintf('%s/%s', $dst, $file));
      }
      else {
        copy($current, sprintf('%s/%s', $dst, $file));
      }
    }
    closedir($dir);
    return $dst;
  }

  /**
   * Create a file from the instance configuration.
   *
   * @param string $tmpPath
   *   The file path to store the file in.
   * @param string $filename
   *   The name of the file.
   * @param \Drupal\masterportal\Entity\MasterportalInstanceInterface $instance
   *   The Masterportal instance.
   * @param string $key
   *   The configuration key.
   * @param string $contentType
   *   The content-type header.
   * @param string $preprocess
   *   The name of the preprocess function to call.
   */
  protected function createDynamicFile($tmpPath, $filename, MasterportalInstanceInterface $instance, $key, $contentType, $preprocess) {
    $this->currentRequest->attributes->set('cacheID', hash('sha256', microtime(TRUE)));
    $this->currentRequest->attributes->set('cacheExclude', TRUE);
    $content = $this->renderer->createResponse($key, $contentType, $preprocess, $instance)->getContent();
    $content = str_replace('/modules/custom/masterportal/libraries/masterportal/', './', $content);
    $content = str_replace(sprintf('/masterportal/%s/', $instance->id()), './', $content);
    $content = str_replace('/masterportal/', './', $content);
    switch ($filename) {

      // Remove the remoteInterface configuration.
      case 'config.js':
        $content = preg_replace('~,\s*?remoteInterface:\s*?\{[^\}]*?\}~ism', '', $content);
        break;

      case 'layerdefinitions.json':
        $content = preg_replace(
          '~"url": "(masterportal/contributions.json.*?)"~',
          '"url": "contributions.json"',
          $content
        );
        break;

      // Copy contribution icons.
      case 'layerstyles.json':
        preg_match_all('~"imageName": "([^"]+?)"~', $content, $matches);
        foreach ($matches[1] as $file) {
          $iconfilename = (function ($path) {
            $parts = explode('/', $path);
            return array_pop($parts);
          })($file);
          $source = sprintf('%s%s', DRUPAL_ROOT, $file);
          $destination = sprintf('%s/img/%s', $tmpPath, $iconfilename);
          copy($source, $destination);
          $content = preg_replace(
            sprintf('~%s~', preg_quote($file, '~')),
            sprintf('img/%s', $iconfilename),
            $content
          );
        }
        break;

      // Replace contribution links with absolute ones.
      case 'contributions.json':
        preg_match_all('~"link": "([^"]+?)"~', $content, $matches);
        foreach ($matches[1] as $url) {
          $absoluteurl = Url::fromUserInput($url, ['absolute' => TRUE])->toString();
          $content = preg_replace(
            sprintf('~%s~', preg_quote($url, '~')),
            $absoluteurl,
            $content
          );
        }
        break;

    }
    file_unmanaged_save_data($content, sprintf('%s/%s', $tmpPath, $filename), FILE_EXISTS_REPLACE);
  }

  /**
   * Packs all files of a Masterportal as a ZIP archive.
   *
   * @param $path
   *   The path in which the ZIP file should get created in.
   *
   * @return string
   *   The path to the zip file.
   */
  protected function createZipFile($path) {
    $target = sprintf('%s/masterportal.zip', file_directory_temp());
    $zip = new ZipArchive();
    $zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $files = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($path),
      RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $name => $file) {
      if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($path) + 1);
        $zip->addFile($filePath, $relativePath);
      }
    }
    $zip->close();
    return $target;
  }

}
