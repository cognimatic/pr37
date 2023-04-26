<?php

namespace Drupal\viewer\Services;

use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;

/**
 * FtpSftp service to process FTP/SFTP connection.
 *
 * @ingroup viewer
 */
class FtpSftp {

  /**
   * Filesystem class.
   *
   * @var League\Flysystem\Filesystem
   */
  protected $fileSystem;

  /**
   * Establish FTP connection.
   */
  public function ftp($settings) {
    $adapter = new FtpAdapter(
      FtpConnectionOptions::fromArray([
        // Required.
        'host' => !empty($settings['host']) ? $settings['host'] : '',
        // Required.
        'root' => !empty($settings['root']) ? $settings['root'] : '/',
        // Required.
        'username' => !empty($settings['username']) ? $settings['username'] : '',
        // Required.
        'password' => !empty($settings['password']) ? $settings['password'] : NULL,
        'port' => !empty($settings['port']) ? (int) $settings['port'] : 21,
        'ssl' => !empty($settings['ssl']),
        'timeout' => !empty($settings['timeout']) ? (int) $settings['timeout'] : 90,
        'utf8' => FALSE,
        'passive' => TRUE,
        'transferMode' => FTP_BINARY,
        // 'windows' or 'unix'
        'systemType' => NULL,
        // True or false.
        'ignorePassiveAddress' => NULL,
        // True or false.
        'timestampsOnUnixListingsEnabled' => FALSE,
        // True.
        'recurseManually' => TRUE,
      ])
    );
    $this->fileSystem = new Filesystem($adapter);
  }

  /**
   * Establish SFTP connection.
   */
  public function sftp($settings) {
    $adapter = new SftpAdapter(
      new SftpConnectionProvider(
        // Host (required)
        $settings['host'],
        // Username (required)
        $settings['username'],
        // Password (optional, default:null) set to null if privateKey is used.
        (empty($settings['use_private_key']) ? $settings['password'] : NULL),
        // Private key (optional, default: null)
        // can be used instead of password, set to null if password is set.
        (!empty($settings['password']) ? NULL : $settings['private_key']),
        // Passphrase (optional, default: null), set to null if
        // privateKey is not used or has no passphrase.
        (!empty($settings['use_private_key']) ? $settings['passphrase'] : NULL),
        // Port (optional, default: 22)
        (!empty($settings['port']) ? $settings['port'] : 2222),
         // Use agent (optional, default: false)
        !empty($settings['user_agent']),
        // Timeout (optional, default: 10)
        (!empty($settings['timeout']) ? $settings['timeout'] : 30),
        // Max tries (optional, default: 4)
        (!empty($settings['maxtries']) ? $settings['maxtries'] : 10)
      ),
      // Root path (required)
      (!empty($settings['root_path']) ? $settings['root_path'] : '/'),
      PortableVisibilityConverter::fromArray([
        'file' => [
          'public' => 0640,
          'private' => 0604,
        ],
        'dir' => [
          'public' => 0740,
          'private' => 7604,
        ],
      ])
    );
    $this->fileSystem = new Filesystem($adapter);
  }

  /**
   * Download a file from a specified path.
   */
  public function downloadFile($path) {
    try {
      return $this->fileSystem->read($path);
    }
    catch (\Exception $e) {
      \Drupal::logger('viewer')->error($e->getMessage());
    }
  }

}
