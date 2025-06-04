# Madiun Blog

A dynamic blog website dedicated to showcasing the beauty, culture, and culinary delights of Madiun City, Indonesia.

## Description

Madiun Blog is a PHP-based web application that allows users to explore and share articles about Madiun City - known as "Kota Pendekar" (City of Warriors). The platform focuses on highlighting local culinary experiences, tourist attractions, cultural heritage, and interesting stories from this charming East Java city.

## Features

- **Article Management**: View, create, edit, and delete blog articles
- **Responsive Design**: Optimized for both desktop and mobile viewing
- **Dynamic Content**: Articles loaded from a database with proper formatting
- **Image Support**: Upload and display images with articles
- **Article Preview**: See snippet previews with "Read More" functionality
- **Fullscreen Article View**: Immersive reading experience with modal popup
- **Categorization**: Organize articles by category
- **Author Attribution**: Track article authors
- **Publication Date**: Display when articles were published

## Screenshots

*[Insert screenshots of your application here]*

## Requirements

- PHP 7.0 or higher
- MySQL/MariaDB
- Web server (Apache/Nginx)

## Installation

1. Clone this repository or download the ZIP file
   ```
   git clone https://github.com/yourusername/madiun-blog.git
   ```

2. Import the database structure
   ```
   mysql -u username -p database_name < database.sql
   ```

3. Configure your database connection in `functions.php`

4. Ensure your web server has write permissions to the `img/` directory

5. Access the website through your local server
   ```
   http://localhost/madiun-blog/
   ```

## Project Structure

```
madiun-blog/
├── index.php          # Main blog page
├── functions.php      # Core functions and database operations
├── style.css          # Main stylesheet
├── tambah_artikel.php # Add new article page
├── edit_artikel.php   # Edit existing article page
├── hapus_artikel.php  # Delete article functionality
├── favicon.ico        # Website favicon
└── img/               # Directory for article images
```

## Usage

### Viewing Articles
- Visit the homepage to see all published articles
- Click "Selengkapnya" (Read More) to view the full article in a modal window
- Close the modal by clicking the X button, pressing ESC, or clicking outside the article

### Managing Articles
- Click "+ Tambah Artikel" to create a new blog post
- Use the "Edit" button to modify an existing article
- Use the "Hapus" button to delete an article (with confirmation)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

[Insert your chosen license here]

## Acknowledgments

- Inspired by the beauty and culture of Madiun City
- Thanks to all contributors and supporters of this project

## Pembuat
- Salsabila Alya Putri Waluyo_230605110015
- Pemrograman Web (C)
- GitHub: SalsabilaAlya26
- salsabillaalyaputri26@gmail.com# artikel_madiun
