<?php

namespace Compose\Session;

use Compose\Traits\CreateStaticInstance;
use League\Flysystem\FilesystemInterface;

class FileSessionHandler implements \SessionHandlerInterface
{
    use CreateStaticInstance;

    /**
     * FileSystem implementation
     *
     * @var FilesystemInterface $files
     */
    protected $files;

    /**
     * Path to the Session files
     *
     * @var string $path
     */
    protected $path;

    /**
     * Create a FileSystemHandler object
     *
     * @param FilesystemInterface $filesystem
     * @param string $path
     */
    public function __construct(FilesystemInterface $filesystem, string $path)
    {
        $this->files = $filesystem;
        $this->path = $path;
    }

    /**
     * Close the session
     * @link https://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

    /**
     * Destroy a session
     * @link https://php.net/manual/en/sessionhandlerinterface.destroy.php
     *
     * @param string $session_id The session ID being destroyed.
     *
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function destroy($session_id)
    {
        // TODO: Implement destroy() method.
    }

    /**
     * Cleanup old sessions
     * @link https://php.net/manual/en/sessionhandlerinterface.gc.php
     *
     * @param int $maxlifetime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     *
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function gc($maxlifetime)
    {
        // TODO: Implement gc() method.
    }

    /**
     * Initialize session
     * @link https://php.net/manual/en/sessionhandlerinterface.open.php
     *
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $name The session name.
     *
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function open($save_path, $name)
    {
        // TODO: Implement open() method.
    }

    /**
     * Read session data
     * @link https://php.net/manual/en/sessionhandlerinterface.read.php
     *
     * @param string $session_id The session id to read data for.
     *
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function read($session_id)
    {
        // TODO: Implement read() method.
    }

    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data) {
        return $this->files->put($this->path . DIRECTORY_SEPARATOR . $sessionId, $data);
    }
}