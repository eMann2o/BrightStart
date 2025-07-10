<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../logo.PNG" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css">
    <script src="https://kit.fontawesome.com/8e98006f77.js" crossorigin="anonymous" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../home.css">
    <meta charset="UTF-8">
    <title>Photo Albums</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>

    <!-- Primary Meta Tags -->
    <title>Picture Gallery - BrightStart</title>
    <meta name="title" content="BrightStart - Empowering Student Success">
    <meta name="description" content="The Bright Start Project is an initiative aim at revolutionizing public education through STEM education, teacher training, and infrastructure development in coastal districts.">

    <!-- Open Graph / Facebook / LinkedIn / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://stccijubilebrightstart.org/">
    <meta property="og:title" content="BrightStart - Empowering Student Success">
    <meta property="og:description" content="The Bright Start Project is an initiative aim at revolutionizing public education through STEM education, teacher training, and infrastructure development in coastal districts.">
    <meta property="og:image" content="https://stccijubilebrightstart.org/ogimage.png">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://stccijubilebrightstart.org/">
    <meta name="twitter:title" content="BrightStart - Empowering Student Success">
    <meta name="twitter:description" content="The Bright Start Project is an initiative aim at revolutionizing public education through STEM education, teacher training, and infrastructure development in coastal districts.">
    <meta name="twitter:image" content="https://stccijubilebrightstart.org/ogimage.png">

    <!-- Responsive and Appearance -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#005792">
    <style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 30px;
    }

    .gallery-stats {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .stat-item {
        text-align: center;
        padding: 15px 20px;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(5px);
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #667eea;
        display: block;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #7f8c8d;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        justify-content: center;
        margin-bottom: 40px;
    }

    .gallery-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        background: white;

        width: 100%;
        max-width: 341px;
        aspect-ratio: 1 / 1; /* keeps it square always */

        display: flex;
        flex-direction: column;
    }


    .gallery-item:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .gallery-item:hover img {
        transform: scale(1.05);
    }

    .item-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
        color: white;
        padding: 15px;
        transform: translateY(100%);
        transition: transform 0.3s ease;
    }

    .gallery-item:hover .item-overlay {
        transform: translateY(0);
    }

    .item-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 5px;
        text-align: center;
    }

    .item-photos-count {
        font-size: 0.9rem;
        opacity: 0.8;
        text-align: center;
    }

    .placeholder-image {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
        opacity: 0.7;
    }

    /* Lightbox */
    .lightbox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .lightbox.active {
        opacity: 1;
        visibility: visible;
    }

    .lightbox-content {
        max-width: 90%;
        max-height: 90%;
        position: relative;
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .lightbox-img-container {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    #lightbox-img {
        width: 100%;
        height: auto;
        max-height: 70vh;
        object-fit: contain;
        opacity: 0;
        transition: opacity 0.5s ease;
    }

    .lightbox-info {
        padding: 20px;
        background: white;
        text-align: center;
        width: 100%;
    }

    .lightbox-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .lightbox-description {
        color: #7f8c8d;
        line-height: 1.6;
        margin-bottom: 10px;
    }

    .lightbox-counter {
        font-size: 0.9rem;
        color: #555;
        margin-top: 5px;
    }

    .lightbox-thumbnails {
        display: flex;
        overflow-x: auto;
        padding: 10px;
        background: #f9f9f9;
        border-top: 1px solid #eee;
        width: 100%;
        gap: 8px;
    }

    .lightbox-thumbnails img {
        width: 60px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.3s, transform 0.3s;
        flex-shrink: 0;
    }

    .lightbox-thumbnails img.active-thumb {
        opacity: 1;
        border: 2px solid #667eea;
        transform: scale(1.05);
    }

    .lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2rem;
        background: rgba(255, 255, 255, 0.8);
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: all 0.3s ease;
        z-index: 10;
    }

    .lightbox-nav:hover {
        background: white;
        transform: translateY(-50%) scale(1.1);
    }

    .lightbox-prev {
        left: 10px;
    }

    .lightbox-next {
        right: 10px;
    }

    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        cursor: pointer;
        font-size: 20px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 11;
    }

    .lightbox-close:hover {
        background: white;
        transform: scale(1.1);
    }

    .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #7f8c8d;
        font-size: 1.1rem;
    }

    .no-results h3 {
        margin-bottom: 10px;
        color: #2c3e50;
    }

    .loading {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200px;
        width: 100%;
        grid-column: 1 / -1;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .error-message {
        text-align: center;
        padding: 40px 20px;
        background: rgba(231, 76, 60, 0.1);
        border-radius: 12px;
        margin: 20px 0;
        color: #c0392b;
        border: 1px solid rgba(231, 76, 60, 0.2);
    }

    .error-message h3 {
        margin-bottom: 10px;
        color: #c0392b;
    }

    .retry-btn {
        background: #667eea;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 15px;
        transition: background 0.3s ease;
    }

    .retry-btn:hover {
        background: #5a67d8;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container {
            padding: 20px;
        }

        .gallery-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .gallery-stats {
            gap: 15px;
        }

        .stat-item {
            padding: 12px 16px;
        }

        .lightbox-nav {
            width: 40px;
            height: 40px;
            font-size: 1.5rem;
        }

        .lightbox-prev {
            left: 5px;
        }

        .lightbox-next {
            right: 5px;
        }
    }

    @media (max-width: 480px) {
        .gallery-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .gallery-stats {
            flex-direction: column;
            gap: 10px;
        }

        .stat-item {
            padding: 10px 15px;
        }
    }

    @media (max-width: 1023px) {
        .nav__menu{
            top: -860px;
        }
        
    }

    .show-menu{
        top: 0px;
    }
    
  </style>
    <style>
        :root {
            --primary: #1a6aa2;
            --secondary: #ff9900;
            --accent: #2a8f5d;
            --dark: #0c2d48;
            --light: #f8f9fa;
            --white: #ffffff; 
            --text: #333333;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light);
            color: var(--text);
            overflow-x: hidden;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
        }

        .section-title h2 {
            font-size: 2.8rem;
            color: var(--primary);
            margin-bottom: 15px;
            font-weight: 700;
        }

        .section-title p {
            font-size: 1.2rem;
            color: #555;
            max-width: 700px;
            margin: 0 auto;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(rgba(12, 45, 72, 0.9), rgba(12, 45, 72, 0.9)),
                url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80') center/cover;
            color: var(--white);
            text-align: center;
            padding: 100px 2rem;
        }

        .cta-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .cta-content h2 {
            font-size: 2.8rem;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .cta-content p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            line-height: 1.7;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: var(--white);
            padding: 80px 30px 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 1.4rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 12px;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--secondary);
        }

        .footer-column p,
        .footer-column a {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.8;
            margin-bottom: 12px;
            transition: var(--transition);
            display: block;
            text-decoration: none;
        }

        .footer-column a:hover {
            color: var(--secondary);
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .social-links a {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            color: var(--white);
            font-size: 1.2rem;
        }

        .social-links a:hover {
            background: var(--secondary);
            transform: translateY(-5px);
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
            font-size: 1rem;
        }
        /* Section Container */
        /* Main Container */
        /* Main Container */
        .main-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 40px;
            padding: 40px 20px;
            background: #ff990026;
            min-height: 100vh;
        }

        /* Content Container */
        .content-container {
            max-width: 1200px;
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 40px;
        }

        /* Header Section */
        .header-section {
            width: 100%;
            text-align: center;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 20px;
        }


        .password-toggle {
            position: absolute;
            right: 50px;
            top: 62%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            font-size: 14px;
            user-select: none;
            background-color: transparent;
            border: none;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <!--==================== HEADER ====================-->
      <header class="header" id="header">
         <nav class="nav container">
            <style>
                .header nav a span{
                    color: #fdcb6e; 
                }
            </style>
            <a href="../" class="nav__logo">
                <img loading="lazy" src="../logo.PNG" alt="logo" style="height: 4rem;">
            </a>
            <a href="../" class="nav__logo" style='font-family: "Lucida Bright", Georgia, serif; font-size: 24px; font-style: normal; font-weight: 700; line-height: 26.4px; '>
                BRIGHT <span>START</span>
            </a>

            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li class="nav__item">                        
                        <a href="../#hero" class="nav__link">Home</a>
                    </li>

                  <li class="nav__item">
                     <a href="../#about" class="nav__link">About</a>
                  </li>
                  <li class="nav__item">
                     <a href="../#district" class="nav__link">Districts</a>
                  </li>
                  <li class="nav__item">
                     <a href="#footer" class="nav__link">Contact</a>
                  </li>

                  <li class="nav__item">
                     <a href="../#faq" class="nav__link">FAQs</a>
                  </li>

                  <li class="nav__item">
                     <a href="#" class="nav__link">Gallery</a>
                  </li>
                </ul>

               <!-- Close button -->
                <div class="nav__close" id="nav-close">
                  <i class="ri-close-line"></i>
                </div>
            </div>

            <div class="nav__actions">
               <!-- Search button -->
               <i class="ri-search-line" id="search-btn" style="display: none;"></i>

               <!-- Login button -->
               <i class="ri-login-box-line nav__login" id="login-btn"></i>

               <!-- Toggle button -->
               <div class="nav__toggle" id="nav-toggle" style="margin: 1rem;">
                  <i class="ri-menu-line"></i>
               </div>
            </div>
         </nav>
      </header>

      <!--==================== SEARCH ====================-->
      <div class="search" id="search">
         <i class="ri-close-line search__close" id="search-close"></i>
      </div>

      <!--==================== LOGIN ====================-->
      <div class="login" id="login">
        <form class="login__form" id="registrationForm" action="../loginpage.php" method="post">
            <h2 class="login__title">Log In</h2>
            
            <div class="login__group">
               <div>
                  <label for="email" class="login__label">Email</label>
                  <input type="email" placeholder="Write your email" name="email" id="email" class="login__input" required>
               </div>
               
               <div>
                  <label for="password" class="login__label">Password</label>
                  <input type="password" placeholder="Enter your password" name="password" id="password" class="login__input" required>
                  <button type="button" class="password-toggle" id="password-toggle"><i class="fa-solid fa-eye"></i></button>
               </div>
            </div>

            <div>   
               <button type="submit" class="login__button">Log In</button>
            </div>
        </form>

        <i class="ri-close-line login__close" id="login-close"></i>
    </div>
    <section class="hero">

        <div class="gallery-stats" id="gallery-stats">
            <div class="stat-item">
                <span class="stat-number" id="albums-count">0</span>
                <span class="stat-label">Albums</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="photos-count">0</span>
                <span class="stat-label">Photos</span>
            </div>
        </div>

        <div class="gallery-grid" id="gallery-grid">
        <div class="loading">
            <div class="loading-spinner"></div>
        </div>
        </div>

        <div class="error-message" id="error-message" style="display: none;">
        <h3>Unable to load gallery</h3>
        <p>There was an error loading the albums. Please try again.</p>
        <button class="retry-btn" onclick="loadGallery()">Retry</button>
        </div>

        <div class="no-results" id="no-results" style="display: none;">
            <h3>No albums found</h3>
            <p>No photo albums are available at the moment.</p>
            </div>
        </div>

        <!-- Lightbox -->
        <div class="lightbox" id="lightbox">
            <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
            <button class="lightbox-nav lightbox-prev" onclick="prevPhoto()">&#8249;</button>
            <button class="lightbox-nav lightbox-next" onclick="nextPhoto()">&#8250;</button>
            <div class="lightbox-img-container">
                <img id="lightbox-img" src="" alt="">
            </div>
            <div class="lightbox-info">
                <div class="lightbox-title" id="lightbox-title"></div>
                <div class="lightbox-description" id="lightbox-description" style="display: none;"></div>
                <div class="lightbox-counter" id="lightbox-counter"></div>
            </div>
            <div class="lightbox-thumbnails" id="lightbox-thumbnails"></div>
            </div>
        </div>
    </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Join Us in Transforming Education</h2>
                    <p>Together, we can create sustainable and meaningful improvements in the educational landscape of
                        Ghana's Western Region. Whether you're an educator, donor, or volunteer, your contribution makes a
                        difference.
                    </p>
                </div>
            </div>
        </section>

    <!-- Footer -->
    <footer id="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Bright Start Project</h3>
                    <p>Transforming education in Ghana's Western Region through STEM education, teacher training, and
                        infrastructure development.</p>
                    <div class="social-links">
                        <a href="../#"><i class="ri-facebook-fill"></i></a>
                        <a href="../#"><i class="ri-twitter-fill"></i></a>
                        <a href="../#"><i class="ri-instagram-fill"></i></a>
                        <a href="../#"><i class="ri-linkedin-fill"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <a href="../#">Home</a>
                    <a href="../#about">About</a>
                    <a href="../#pillars">Pillars</a>
                    <a href="../#district">Districts</a>
                </div>
                <div class="footer-column">
                    <h3>Contact Us</h3>
                    <p><i class="ri-map-pin-fill"></i> Sekondi-Takoradi, Ghana</p>
                    <p> <a href="mailto:stcci.ghana@gmail.com"><i class="ri-mail-fill"></i>&nbsp;stcci.ghana@gmail.com</a></p>
                    <p><a href="tel:+233244694781"><i class="ri-phone-fill"></i>&nbsp; +233 24 469 4781</a></p>
                </div>
               
            </div>
            <div class="copyright">
                <p>&copy; 2025 Bright Start Project. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script>
    let currentAlbumPhotos = [];
    let currentPhotoIndex = 0;
    let albumsData = [];
    let totalPhotos = 0;

    // Load gallery on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadGallery();
    });

    async function loadGallery() {
      const galleryGrid = document.getElementById('gallery-grid');
      const errorMessage = document.getElementById('error-message');
      const noResults = document.getElementById('no-results');
      const galleryStats = document.getElementById('gallery-stats');
      
      // Reset states
      errorMessage.style.display = 'none';
      noResults.style.display = 'none';
      galleryStats.style.display = 'none';
      
      // Show loading
      galleryGrid.innerHTML = '<div class="loading"><div class="loading-spinner"></div></div>';
      
      try {
        const response = await fetch('../admin/get_albums.php');
        const data = await response.json();
        
        if (data.success && data.albums) {
          albumsData = data.albums;
          await renderGallery(albumsData);
          updateStats();
        } else {
          throw new Error(data.message || 'Failed to load albums');
        }
      } catch (error) {
        console.error('Error loading gallery:', error);
        galleryGrid.innerHTML = '';
        errorMessage.style.display = 'block';
      }
    }

    async function renderGallery(albums) {
      const galleryGrid = document.getElementById('gallery-grid');
      const noResults = document.getElementById('no-results');
      
      if (albums.length === 0) {
        galleryGrid.innerHTML = '';
        noResults.style.display = 'block';
        return;
      }
      
      noResults.style.display = 'none';
      
      // Count total photos across all albums
      totalPhotos = 0;
      for (const album of albums) {
        try {
          const response = await fetch(`../get_album_photos.php?album_id=${album.id}`);
          const data = await response.json();
          if (data.success && data.photos) {
            totalPhotos += data.photos.length;
          }
        } catch (error) {
          console.error(`Error fetching photos for album ${album.id}:`, error);
        }
      }
      
      galleryGrid.innerHTML = albums.map(album => {
        const coverImage = album.cover 
          ? `../admin/uploads/${album.cover}`
          : null;
        
        return `
          <div class="gallery-item" onclick="viewAlbum(${album.id}, '${album.name}')">
            ${coverImage 
              ? `<img src="${coverImage}" alt="${album.name}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">`
              : ''
            }
            <div class="placeholder-image" style="${coverImage ? 'display: none;' : ''}">
              📸
            </div>
            <div class="item-overlay">
              <div class="item-title">${album.name}</div>
              <div class="item-photos-count">Click to view photos</div>
            </div>
          </div>
        `;
      }).join('');
    }

    function updateStats() {
      const albumsCount = document.getElementById('albums-count');
      const photosCount = document.getElementById('photos-count');
      const galleryStats = document.getElementById('gallery-stats');
      
      albumsCount.textContent = albumsData.length;
      photosCount.textContent = totalPhotos;
      galleryStats.style.display = 'flex';
      
      // Animate counters
      animateCounter(albumsCount, albumsData.length);
      animateCounter(photosCount, totalPhotos);
    }

    function animateCounter(element, target) {
      let current = 0;
      const increment = target / 30; // 30 frames
      const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
          current = target;
          clearInterval(timer);
        }
        element.textContent = Math.floor(current);
      }, 50);
    }

    async function viewAlbum(albumId, albumName) {
      try {
        const response = await fetch(`../get_album_photos.php?album_id=${albumId}`);
        const data = await response.json();
        
        if (data.success && data.photos && data.photos.length > 0) {
          // Map photo objects to expected format
          currentAlbumPhotos = data.photos.map(photo => ({
            src: `../admin/uploads/${photo.filename}`,
            title: photo.title || photo.original_name || 'Untitled',
            description: photo.original_name || 'No description available'
          }));
          currentPhotoIndex = 0;
          showPhoto(currentPhotoIndex);
        } else {
          alert('No photos found in this album.');
        }
      } catch (error) {
        console.error('Error loading album photos:', error);
        alert('Error loading photos. Please try again.');
      }
    }

    function showPhoto(index) {
      const lightbox = document.getElementById("lightbox");
      const img = document.getElementById("lightbox-img");
      const title = document.getElementById("lightbox-title");
      const desc = document.getElementById("lightbox-description");
      const counter = document.getElementById("lightbox-counter");
      const thumbnails = document.getElementById("lightbox-thumbnails");

      const item = currentAlbumPhotos[index];

      // Fade out current image
      img.style.opacity = 0;
      
      setTimeout(() => {
        img.src = item.src;
        img.alt = item.title;
        img.onload = () => {
          img.style.opacity = 1;
        };
        img.onerror = () => {
          img.style.opacity = 1;
          img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xNzUgMTI1SDE4NVYxNzVIMTc1VjEyNVoiIGZpbGw9IiM5Q0EzQUYiLz4KPC9zdmc+';
        };
      }, 300);

      title.textContent = item.title;
      desc.textContent = item.description;
      counter.textContent = `${index + 1} / ${currentAlbumPhotos.length}`;

      // Generate thumbnails
      thumbnails.innerHTML = "";
      currentAlbumPhotos.forEach((photo, i) => {
        const thumb = document.createElement("img");
        thumb.src = photo.src;
        thumb.loading = "lazy";
        thumb.className = i === index ? "active-thumb" : "";
        thumb.onclick = () => {
          currentPhotoIndex = i;
          showPhoto(i);
        };
        thumb.onerror = () => {
          thumb.style.display = 'none';
        };
        thumbnails.appendChild(thumb);
      });

      preloadAdjacentImages(index);

      lightbox.classList.add("active");
      document.body.style.overflow = "hidden";
    }

    function nextPhoto() {
      if (currentPhotoIndex < currentAlbumPhotos.length - 1) {
        currentPhotoIndex++;
        showPhoto(currentPhotoIndex);
      }
    }

    function prevPhoto() {
      if (currentPhotoIndex > 0) {
        currentPhotoIndex--;
        showPhoto(currentPhotoIndex);
      }
    }

    function closeLightbox() {
      const lightbox = document.getElementById("lightbox");
      lightbox.classList.remove("active");
      document.body.style.overflow = "";
    }

    function preloadAdjacentImages(index) {
      if (currentAlbumPhotos[index + 1]) {
        const nextImg = new Image();
        nextImg.src = currentAlbumPhotos[index + 1].src;
      }
      if (currentAlbumPhotos[index - 1]) {
        const prevImg = new Image();
        prevImg.src = currentAlbumPhotos[index - 1].src;
      }
    }

    // Touch/Swipe support for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    function handleSwipe() {
      if (touchEndX < touchStartX - 50) {
        nextPhoto();
      }
      if (touchEndX > touchStartX + 50) {
        prevPhoto();
      }
    }

    const lightbox = document.getElementById("lightbox");
    lightbox.addEventListener("touchstart", (e) => {
      touchStartX = e.changedTouches[0].screenX;
    });

    lightbox.addEventListener("touchend", (e) => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    });

    // Keyboard navigation
    document.addEventListener("keydown", (e) => {
      if (!document.getElementById("lightbox").classList.contains("active")) return;
      
      switch(e.key) {
        case "ArrowRight":
          nextPhoto();
          break;
        case "ArrowLeft":
          prevPhoto();
          break;
        case "Escape":
          closeLightbox();
          break;
      }
    });

    // Close lightbox when clicking outside the content
    lightbox.addEventListener("click", (e) => {
      if (e.target === lightbox) {
        closeLightbox();
      }
    });
  </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            /*=============== SHOW MENU ===============*/
            const navMenu = document.getElementById('nav-menu'),
                navToggle = document.getElementById('nav-toggle'),
                navClose = document.getElementById('nav-close');

            if (navToggle && navMenu) {
                navToggle.addEventListener('click', () => {
                    navMenu.classList.add('show-menu');
                });
            }

            if (navClose && navMenu) {
                navClose.addEventListener('click', () => {
                    navMenu.classList.remove('show-menu');
                });
            }

            /*=============== SEARCH ===============*/
            const search = document.getElementById('search'),
                searchBtn = document.getElementById('search-btn'),
                searchClose = document.getElementById('search-close');

            if (searchBtn && search) {
                searchBtn.addEventListener('click', () => {
                    search.classList.add('show-search');
                });
            }

            if (searchClose && search) {
                searchClose.addEventListener('click', () => {
                    search.classList.remove('show-search');
                });
            }

            /*=============== LOGIN ===============*/
            const login = document.getElementById('login'),
                loginBtn = document.getElementById('login-btn'),
                loginClose = document.getElementById('login-close');

            if (loginBtn && login) {
                loginBtn.addEventListener('click', () => {
                    login.classList.add('show-login');
                });
            }

            if (loginClose && login) {
                loginClose.addEventListener('click', () => {
                    login.classList.remove('show-login');
                });
            }

            // Toggle password visibility
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('password-toggle');

            if (passwordToggle && passwordInput) {
                passwordToggle.addEventListener('click', function() {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        passwordToggle.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
                    } else {
                        passwordInput.type = 'password';
                        passwordToggle.innerHTML = '<i class="fa-solid fa-eye"></i>';
                    }
                });
            }

            
        });
    </script>
</body>

</html>


