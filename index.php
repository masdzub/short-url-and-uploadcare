<?php

// Include the configuration file
include 'config.php'; // Ensure this file is in the same directory as your main file


// Initialize variables
$shortened_url = '';
$error_message = '';
$custom_slug = ''; // Holds the custom slug if provided
$uploaded_url = ''; // Holds the uploaded image URL

// Check if custom slug is enabled via ?custom=true
$custom_enabled = isset($_GET['custom']) && $_GET['custom'] === 'true';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle URL shortening
    if (isset($_POST['url'])) {
        $long_url = filter_var($_POST['url'], FILTER_VALIDATE_URL);

        // If custom slug is enabled, get the slug from the form input
        if ($custom_enabled && isset($_POST['custom_slug']) && !empty($_POST['custom_slug'])) {
            $custom_slug = filter_var($_POST['custom_slug'], FILTER_SANITIZE_STRING);
        }

        if ($long_url) {
            // Set up the API request, including the custom slug if provided
            $api_url = $yourls_url . '?signature=' . $yourls_signature . '&action=shorturl&format=json&url=' . urlencode($long_url);
            if (!empty($custom_slug)) {
                $api_url .= '&keyword=' . urlencode($custom_slug); // Append custom slug (keyword)
            }

            // Perform the API request
            $response = file_get_contents($api_url);
            $result = json_decode($response, true);

            // Check if the API request was successful
            if (isset($result['shorturl'])) {
                $shortened_url = $result['shorturl'];
            } else {
                $error_message = isset($result['message']) ? $result['message'] : 'There was an error shortening the URL. Please try again.';
            }
        } else {
            $error_message = 'Please enter a valid URL.';
        }
    }

    // Handle image upload from clipboard
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];

        // Ensure the file is successfully uploaded
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Get the file path and other details
            $file_path = $file['tmp_name'];
            $file_name = $file['name'];

            // Create a cURL handle for Uploadcare
            $ch = curl_init();
            $uploadcare_url = 'https://upload.uploadcare.com/base/';

            // Prepare the file data for upload
            $post_data = [
                'UPLOADCARE_PUB_KEY' => $uploadcare_public_key,
                'UPLOADCARE_STORE' => '1', // Set to '1' to store the file after upload
                'file' => new CURLFile($file_path, $file['type'], $file_name)
            ];

            // Set the cURL options
            curl_setopt($ch, CURLOPT_URL, $uploadcare_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the cURL request
            $upload_response = curl_exec($ch);

            // Check if any errors occurred
            if (curl_errno($ch)) {
                $error_message = 'Uploadcare cURL error: ' . curl_error($ch);
            } else {
                // Decode the response from Uploadcare
                $upload_result = json_decode($upload_response, true);

                // Display the URL of the uploaded file
                if (isset($upload_result['file'])) {
                    $uploaded_url = 'https://ucarecdn.com/' . $upload_result['file'] . '/';
                } else {
                    $error_message = 'File upload failed!';
                }
            }

            // Close the cURL handle
            curl_close($ch);
        } else {
            $error_message = 'Error uploading file.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener and Image Upload</title>
    <link rel="icon" href="https://ucarecdn.com/90cb273d-ec6c-4516-adc4-cfc07442805f/-/scale_crop/30x30/" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
        /* Add smooth transitions for dark mode */
        body, header, footer, main, button, input, div {
            transition: all 0.3s ease;
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            light: '#6482AD',
                            dark: '#6366F1'
                        },
                        secondary: {
                            light: '#8BA3C7',
                            dark: '#4B5563'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-primary-light/10 to-primary-light/20 dark:from-gray-900 dark:to-gray-800 flex flex-col min-h-screen text-gray-800 dark:text-gray-200">

    <!-- Dark Mode Toggle -->
    <div class="absolute top-4 right-4">
        <button id="dark-mode-toggle" class="bg-secondary-light/30 dark:bg-gray-700 text-primary-light dark:text-gray-200 px-4 py-2 rounded-full focus:outline-none hover:bg-secondary-light/50 dark:hover:bg-gray-600 transition-colors">
            🌞 Light
        </button>
    </div>

    <!-- Header -->
    <header class="bg-primary-light dark:bg-gray-800 text-white py-6 px-4 shadow-lg">
        <div class="container mx-auto">
            <h1 class="text-4xl font-bold text-center">
                <a href="https://s.masdzub.com" class="hover:underline">
                    <img src="https://ucarecdn.com/90cb273d-ec6c-4516-adc4-cfc07442805f/-/scale_crop/50x50/" alt="s.masdzub.com Logo" class="inline-block" />
                </a>
            </h1>
        </div>
    </header>


    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8 flex flex-col space-y-8">
        <!-- URL Shortener Section -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg transition-all duration-300 transform hover:shadow-xl">
            <h2 class="text-2xl font-semibold mb-6 text-primary-light dark:text-primary-dark">Shorten URL</h2>
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enter URL:</label>
                    <input type="url" name="url" id="url" class="w-full px-4 py-2 bg-secondary-light/10 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-secondary-light dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-primary-light dark:focus:border-primary-dark transition duration-200" required>
                </div>

                <?php if ($custom_enabled): ?>
                <div>
                    <label for="custom_slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Slug:</label>
                    <input type="text" name="custom_slug" id="custom_slug" class="w-full px-4 py-2 bg-secondary-light/10 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-secondary-light dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-primary-light dark:focus:border-primary-dark transition duration-200" placeholder="Enter a custom slug (optional)">
                </div>
                <?php endif; ?>
                
                <div>
                    <button type="submit" class="w-full bg-primary-light dark:bg-primary-dark text-white px-4 py-3 rounded-lg hover:bg-primary-light/80 dark:hover:bg-indigo-600 transition duration-200 font-medium text-lg">Shorten URL</button>
                </div>
            </form>

            <!-- Display shortened URL or error message -->
            <?php if (!empty($shortened_url)): ?>
                <div class="mt-4 p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                    <p class="text-green-700 dark:text-green-300 font-medium mb-2">Shortened URL:</p>
                    <div class="flex items-center space-x-2">
                        <a href="<?= $shortened_url ?>" class="text-primary-light dark:text-blue-400 underline break-all" id="short-url"><?= $shortened_url ?></a>
                        <button class="bg-primary-light dark:bg-primary-dark text-white px-3 py-1 rounded-md hover:bg-primary-light/80 dark:hover:bg-indigo-600 transition duration-200" data-clipboard-text="<?= $shortened_url ?>" id="copy-btn">Copy</button>
                    </div>
                </div>
            <?php elseif (!empty($error_message)): ?>
                <div class="mt-4 p-4 bg-red-100 dark:bg-red-900 rounded-lg text-red-700 dark:text-red-300">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Image Upload Section -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg transition-all duration-300 transform hover:shadow-xl">
            <h2 class="text-2xl font-semibold mb-6 text-primary-light dark:text-primary-dark">Upload Image</h2>
            <form action="" method="POST" enctype="multipart/form-data" id="upload-form" class="space-y-4">
                <div id="drop-area" class="h-40 border-2 border-dashed border-primary-light dark:border-primary-dark rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 relative cursor-pointer hover:bg-secondary-light/10 dark:hover:bg-gray-700 transition duration-200">
                    <input type="file" name="file" id="file" class="absolute inset-0 opacity-0 cursor-pointer" required>
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-primary-light dark:text-primary-dark" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="mt-2 text-sm">Drop image here or click to upload</p>
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full bg-primary-light dark:bg-primary-dark text-white px-4 py-3 rounded-lg hover:bg-primary-light/80 dark:hover:bg-indigo-600 transition duration-200 font-medium text-lg">Upload File</button>
                </div>
            </form>

            <!-- Display uploaded image URL or error message -->
            <?php if (!empty($uploaded_url)): ?>
                <div class="mt-4 p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                    <p class="text-green-700 dark:text-green-300 font-medium mb-2">Uploaded Image URL:</p>
                    <div class="flex items-center space-x-2">
                        <a href="<?= $uploaded_url ?>" class="text-primary-light dark:text-blue-400 underline break-all" target="_blank"><?= $uploaded_url ?></a>
                        <button class="bg-primary-light dark:bg-primary-dark text-white px-3 py-1 rounded-md hover:bg-primary-light/80 dark:hover:bg-indigo-600 transition duration-200" data-clipboard-text="<?= $uploaded_url ?>" id="copy-upload-btn">Copy</button>
                    </div>
                </div>
            <?php elseif (!empty($error_message)): ?>
                <div class="mt-4 p-4 bg-red-100 dark:bg-red-900 rounded-lg text-red-700 dark:text-red-300">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Full-width Paste Area -->
        <div id="clipboard-area" class="flex-grow bg-gradient-to-r from-primary-light to-secondary-light dark:from-gray-700 dark:to-gray-800 rounded-3xl shadow-2xl overflow-hidden">
            <div class="h-full flex flex-col items-center justify-center text-white p-8 text-center cursor-pointer transition duration-300 hover:bg-opacity-90">
                <svg class="h-24 w-24 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="text-3xl font-bold mb-2">Paste Image Here</h3>
                <p class="text-xl opacity-80">Use Ctrl+V to paste your image</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary-light dark:bg-gray-900 text-white py-4">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2024 URL Shortener & Image Upload. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Clipboard.js for URL copying
        const clipboard = new ClipboardJS('#copy-btn, #copy-upload-btn');
        clipboard.on('success', function(e) {
            const originalText = e.trigger.textContent;
            e.trigger.textContent = 'Copied!';
            setTimeout(() => {
                e.trigger.textContent = originalText;
            }, 2000);
        });

        // Handle file drop and selection
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropArea.classList.add('bg-secondary-light/20', 'dark:bg-gray-700');
        }

        function unhighlight(e) {
            dropArea.classList.remove('bg-secondary-light/20', 'dark:bg-gray-700');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
        }

        // Handle pasting images from the clipboard
        const clipboardArea = document.getElementById('clipboard-area');
        const uploadForm = document.getElementById('upload-form');

        clipboardArea.addEventListener('paste', function(e) {
            const items = e.clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                if (item.kind === 'file') {
                    const file = item.getAsFile();
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    uploadForm.file.files = dataTransfer.files;
                    uploadForm.submit();
                }
            }
        });

        clipboardArea.addEventListener('click', function() {
            alert('Paste an image (Ctrl + V) to upload it.');
        });

        // Dark Mode Toggle Script
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const htmlElement = document.documentElement;

        // Check for saved theme preference or default to light mode
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            htmlElement.classList.add('dark');
            darkModeToggle.textContent = '🌙 Dark';
        } else {
            htmlElement.classList.remove('dark');
            darkModeToggle.textContent = '🌞 Light';
        }

        // Toggle dark mode
        darkModeToggle.addEventListener('click', () => {
            htmlElement.classList.toggle('dark');
            if (htmlElement.classList.contains('dark')) {
                localStorage.theme = 'dark';
                darkModeToggle.textContent = '🌙 Dark';
            } else {
                localStorage.theme = 'light';
                darkModeToggle.textContent = '🌞 Light';
            }
        });
    </script>
</body>
</html>