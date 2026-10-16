<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by App\Services\BackupService for anything that should stop a
 * backup or restore in its tracks with a message safe to show an admin
 * directly (invalid upload, corrupt archive, a shell command that failed).
 */
class BackupException extends RuntimeException {}
