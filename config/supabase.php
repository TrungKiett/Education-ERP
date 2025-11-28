<?php
// Supabase Storage configuration
// You need to install supabase-php package: composer require supabase/supabase-php
// Or use direct REST API calls

// Supabase configuration constants
define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'https://your-project.supabase.co');
define('SUPABASE_KEY', getenv('SUPABASE_KEY') ?: 'your-anon-key');
define('SUPABASE_BUCKET', getenv('SUPABASE_BUCKET') ?: 'enrollment-documents');

/**
 * Upload file to Supabase Storage
 * @param string $filePath Local file path
 * @param string $fileName Desired file name in storage
 * @return string|false File URL on success, false on failure
 */
function uploadToSupabase($filePath, $fileName) {
    // Check if file exists
    if (!file_exists($filePath)) {
        return false;
    }
    
    // Generate unique filename to avoid conflicts
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    $uniqueFileName = uniqid() . '_' . time() . '.' . $extension;
    $storagePath = 'enrollments/' . $uniqueFileName;
    
    // Option 1: Using Supabase PHP client (if installed via Composer)
    // Uncomment if you have supabase/supabase-php installed
    /*
    try {
        $supabase = new \Supabase\Client(SUPABASE_URL, SUPABASE_KEY);
        $storage = $supabase->storage();
        $bucket = $storage->from(SUPABASE_BUCKET);
        
        $fileContent = file_get_contents($filePath);
        $result = $bucket->upload($storagePath, $fileContent, [
            'contentType' => mime_content_type($filePath),
            'upsert' => false
        ]);
        
        if ($result) {
            return SUPABASE_URL . '/storage/v1/object/public/' . SUPABASE_BUCKET . '/' . $storagePath;
        }
    } catch (Exception $e) {
        error_log("Supabase upload error: " . $e->getMessage());
        return false;
    }
    */
    
    // Option 2: Using direct REST API (works without Composer)
    $url = SUPABASE_URL . '/storage/v1/object/' . SUPABASE_BUCKET . '/' . $storagePath;
    
    $fileContent = file_get_contents($filePath);
    $mimeType = mime_content_type($filePath);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: ' . $mimeType,
        'x-upsert: false'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        // Return public URL
        return SUPABASE_URL . '/storage/v1/object/public/' . SUPABASE_BUCKET . '/' . $storagePath;
    }
    
    error_log("Supabase upload failed. HTTP Code: $httpCode, Response: $response");
    return false;
}

/**
 * Delete file from Supabase Storage
 * @param string $fileUrl Full URL of the file
 * @return bool Success status
 */
function deleteFromSupabase($fileUrl) {
    // Extract path from URL
    $pattern = '/\/storage\/v1\/object\/public\/' . SUPABASE_BUCKET . '\/(.+)$/';
    if (preg_match($pattern, $fileUrl, $matches)) {
        $filePath = $matches[1];
        
        $url = SUPABASE_URL . '/storage/v1/object/' . SUPABASE_BUCKET . '/' . $filePath;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . SUPABASE_KEY
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode >= 200 && $httpCode < 300;
    }
    
    return false;
}

