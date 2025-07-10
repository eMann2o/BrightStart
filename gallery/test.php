<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Learning Management System</title>
    <style>
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
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
            aspect-ratio: 1;
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
            margin-bottom: 0;
            text-align: center;
        }

        .item-category {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .item-date {
            font-size: 0.8rem;
            opacity: 0.7;
            margin-top: 5px;
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
        }

        .lightbox img {
            width: 100%;
            height: auto;
            max-height: 70vh;
            object-fit: contain;
        }

        .lightbox-info {
            padding: 20px;
            background: white;
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

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .gallery-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 12px;
            }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gallery</h1>
            <p>Browse through our collection of educational resources and memories</p>
        </div>

        <div class="gallery-grid" id="gallery-grid">
            <!-- Gallery items will be populated by JavaScript -->
        </div>

        <div class="no-results" id="no-results" style="display: none;">
            <h3>No images found</h3>
            <p>Try adjusting your search terms or filters</p>
        </div>
    </div>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
            <img id="lightbox-img" src="" alt="">
            <div class="lightbox-info">
                <div class="lightbox-title" id="lightbox-title"></div>
                <div class="lightbox-description" id="lightbox-description"></div>
            </div>
        </div>
    </div>

    <script>
        // Sample gallery data - replace with your actual data
        const galleryData = [
            {
                id: 1,
                src: "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&h=400&fit=crop",
                title: "Computer Science Workshop",
                description: "Students participating in an interactive computer science workshop focusing on modern web development technologies."
            },
            {
                id: 2,
                src: "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop",
                title: "Mathematics Course",
                description: "Advanced mathematics course covering calculus and linear algebra concepts with practical applications."
            },
            {
                id: 3,
                src: "https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=600&h=400&fit=crop",
                title: "Student Graduation",
                description: "Celebrating our graduating students and their achievements in various academic programs."
            },
            {
                id: 4,
                src: "https://images.unsplash.com/photo-1497486751825-1233686d5d80?w=600&h=400&fit=crop",
                title: "Science Laboratory",
                description: "State-of-the-art science laboratory equipped with modern equipment for hands-on learning."
            },
            {
                id: 5,
                src: "https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=600&h=400&fit=crop",
                title: "Literature Seminar",
                description: "Annual literature seminar featuring guest speakers and interactive discussions."
            },
            {
                id: 6,
                src: "https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=600&h=400&fit=crop",
                title: "Physics Course",
                description: "Comprehensive physics course covering mechanics, thermodynamics, and electromagnetism."
            },
            {
                id: 7,
                src: "https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=600&h=400&fit=crop",
                title: "Study Group",
                description: "Students collaborating in study groups to enhance their learning experience."
            },
            {
                id: 8,
                src: "https://images.unsplash.com/photo-1562774053-701939374585?w=600&h=400&fit=crop",
                title: "Campus Library",
                description: "Modern library facility with extensive digital and physical resources for research."
            },
            {
                id: 9,
                src: "https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&h=400&fit=crop",
                title: "Annual Conference",
                description: "Annual academic conference bringing together educators and researchers."
            }
        ];

        // Initialize gallery
        function initGallery() {
            renderGallery();
            setupEventListeners();
        }

        // Render gallery items
        function renderGallery() {
            const galleryGrid = document.getElementById('gallery-grid');
            
            // Clear gallery
            galleryGrid.innerHTML = '';

            // Create gallery items
            galleryData.forEach(item => {
                const galleryItem = document.createElement('div');
                galleryItem.className = 'gallery-item';
                galleryItem.onclick = () => openLightbox(item);

                galleryItem.innerHTML = `
                    <img src="${item.src}" alt="${item.title}" loading="lazy">
                    <div class="item-overlay">
                        <div class="item-title">${item.title}</div>
                    </div>
                `;

                galleryGrid.appendChild(galleryItem);
            });
        }

        // Setup event listeners
        function setupEventListeners() {
            // Lightbox close on background click
            document.getElementById('lightbox').addEventListener('click', (e) => {
                if (e.target.id === 'lightbox') {
                    closeLightbox();
                }
            });

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeLightbox();
                }
            });
        }

        // Open lightbox
        function openLightbox(item) {
            const lightbox = document.getElementById('lightbox');
            const lightboxImg = document.getElementById('lightbox-img');
            const lightboxTitle = document.getElementById('lightbox-title');
            const lightboxDescription = document.getElementById('lightbox-description');

            lightboxImg.src = item.src;
            lightboxImg.alt = item.title;
            lightboxTitle.textContent = item.title;
            lightboxDescription.textContent = item.description;

            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Close lightbox
        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', initGallery);
    </script>
</body>
</html>
