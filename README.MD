# URL Shortener & Image Upload

This project is a simple web application that allows users to shorten URLs and upload images using Uploadcare and YOURLS API. The application is built with PHP and uses Tailwind CSS for styling.

## Features

- Shorten URLs using YOURLS API
- Upload images using Uploadcare
- Copy shortened URLs and uploaded image URLs to the clipboard
- Support for custom URL slugs
- User-friendly interface

## Technologies Used

- PHP
- YOURLS API
- Uploadcare API
- Tailwind CSS
- Clipboard.js

## Prerequisites

- A web server with PHP support (e.g., Apache, Nginx)
- YOURLS installation with an API endpoint
- Uploadcare account for API keys

## Setup Instructions

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/masdzub/short-url-and-uploadcare.git
   cd short-url-and-uploadcare
   ```

2. **Configuration**:
   - Copy the `config-sample.php` to `config.php`:
   ```bash
   cp config-sample.php config.php
   ```
   - Open the `config.php` file and add the following code:
   ```php
   <?php
   // YOURLS API Configuration
   define('YOURLS_URL', 'https://your-yourls-domain.com/yourls-api.php'); // Replace with your YOURLS API URL
   define('YOURLS_SIGNATURE', 'YOUR_API_SIGNATURE_HERE'); // Replace with your actual YOURLS API signature

   // Uploadcare API Configuration
   define('UPLOADCARE_PUBLIC_KEY', 'YOUR_UPLOADCARE_PUBLIC_KEY'); // Replace with your Uploadcare public key
   define('UPLOADCARE_SECRET_KEY', 'YOUR_UPLOADCARE_SECRET_KEY'); // Replace with your Uploadcare secret key
   ?>
   ```
   - Make sure to replace the placeholders with your actual API URLs and keys.

3. **Deploy the Application**:
   - Upload the files to your web server, ensuring that `config.php` and the main application file are accessible.

4. **Access the Application**:
   - Open your web browser and navigate to the URL where the application is hosted.

## Usage Instructions

1. **Shorten a URL**:
   - Enter the URL you want to shorten in the "Enter URL" field.
   - Optionally, you can provide a custom slug.
   - Click on "Shorten URL" to receive the shortened link.

2. **Upload an Image**:
   - You can upload an image by dragging it into the designated area or by clicking to select a file.
   - You can also paste an image directly from your clipboard.
   - After a successful upload, the URL of the uploaded image will be displayed.

3. **Copy URLs**:
   - Use the "Copy" button next to the shortened URL or uploaded image URL to copy it to your clipboard.

## Prevent Crawling

The application includes a meta tag to prevent search engines from indexing the page:

```html
<meta name="robots" content="noindex, nofollow">
```

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.

## Acknowledgements

- [YOURLS](https://yourls.org/) - Your Own URL Shortener
- [Uploadcare](https://uploadcare.com/) - Image Upload and Management
- [Tailwind CSS](https://tailwindcss.com/) - Utility-first CSS Framework
- [Clipboard.js](https://clipboardjs.com/) - Copy to Clipboard Library

### Additional Notes
- Ensure that you create a `config-sample.php` file in your project directory that includes the template code without sensitive information. This will guide users in configuring their own `config.php`. 
- Make sure to adjust any instructions or links as necessary to fit your project's structure or requirements.