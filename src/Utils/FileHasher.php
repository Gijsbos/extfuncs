<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use Exception;
use ReflectionClass;
use gijsbos\ExtFuncs\Exceptions\FileNotFoundException;

/**
 * FileHasher
 */
abstract class FileHasher
{
    const MD5 = "md5";
    const SHA1 = "sha1";
    const XXH3 = "xxh3"; // Fastest as of 23 May 2023

    /**
     * createFileHashArray
     */
    private static function createFileHashArray(string $filePath, string $hashAlgorithm) : array
    {
        // Create file hash array
        $fileHashArray = [];

        // Check if filePath is dir
        if(is_dir($filePath))
        {
            $filePath = !str_ends_with($filePath, DIRECTORY_SEPARATOR) ? $filePath . "/" : $filePath;

            // Scan directory
            $cdir = scandir($filePath);
            foreach ($cdir as $value)
            {
                // Check if is not . or ..
                if(!in_array($value, [".",".."]))
                {
                    // Set path
                    $path = $filePath . $value;

                    // Parse path; paths are unique keys so + avoids copying the array on every merge
                    $fileHashArray += self::createFileHashArray($path, $hashAlgorithm);
                }
            }
        }
        else if(is_file($filePath))
        {
            switch($hashAlgorithm)
            {
                case FileHasher::SHA1:
                    $fileHashArray[$filePath] = sha1_file($filePath);
                break;
                case FileHasher::MD5:
                    $fileHashArray[$filePath] = md5_file($filePath);
                break;
                case FileHasher::XXH3:
                default:
                    $fileHashArray[$filePath] = hash_file('xxh3', $filePath);
            }
        }

        // Return result
        return $fileHashArray;
    }

    /**
     * normalizeAlgorithm
     */
    private static function normalizeAlgorithm(string $hashAlgorithm) : string
    {
        $hashAlgorithm = strtolower($hashAlgorithm);

        if(!in_array($hashAlgorithm, (new ReflectionClass(self::class))->getConstants(), true))
            throw new Exception(sprintf("Could not create file hash array, unknown algorithm '%s'", $hashAlgorithm));

        return $hashAlgorithm;
    }

    /**
     * hash
     */
    public static function hash(string $filePath, string $hashAlgorithm = FileHasher::MD5) : string
    {
        $hashAlgorithm = self::normalizeAlgorithm($hashAlgorithm);

        // Check if path exists
        if(!is_file($filePath) && !is_dir($filePath))
            throw new FileNotFoundException("%s could not locate file path '%s'", __METHOD__, $filePath);

        // Hash files
        $fileHashArray = self::createFileHashArray($filePath, $hashAlgorithm);

        // Return combined hash
        switch($hashAlgorithm)
        {
            case FileHasher::SHA1:
                return hash("sha1", implode($fileHashArray));
            case FileHasher::MD5:
                return hash("md5", implode($fileHashArray));
            case FileHasher::XXH3:
            default:
                return hash("xxh3", implode($fileHashArray));       
        }
    }

    /**
     * hashFilePathArray
     */
    public static function hashFilePathArray($filePathArray, string $hashAlgorithm = FileHasher::MD5) : string
    {
        $filePathArray = is_string($filePathArray) ? [$filePathArray] : $filePathArray;
        $hashAlgorithm = self::normalizeAlgorithm($hashAlgorithm);

        // Set hash array
        $fileHashArray = [];

        // Hash filePaths
        foreach($filePathArray as $filePath)
            array_push($fileHashArray, self::hash($filePath, $hashAlgorithm));

        // Return combined hash
        switch($hashAlgorithm)
        {
            case FileHasher::SHA1:
                return hash("sha1", implode($fileHashArray));
            case FileHasher::XXH3:
                return hash("xxh3", implode($fileHashArray));
            case FileHasher::MD5:
            default:
                return hash("md5", implode($fileHashArray));
        }
    }
}