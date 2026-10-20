// Theme Toggle Script for PENGADUAN DIGITAL

document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('theme-toggle');
    const htmlRoot = document.documentElement;

    // Theme color configurations
    const lightTheme = {
        '--primary-color': '#00a86b',
        '--secondary-color': '#4ecdc4',
        '--bg-color': '#f4f4f4',
        '--text-color': '#333333',
        '--accent-color': '#ff6b6b',
        '--dark-bg': '#ffffff',
        '--card-bg': 'rgba(0, 0, 0, 0.05)'
    };

    const darkTheme = {
        '--primary-color': '#00ff88',
        '--secondary-color': '#00ccff',
        '--bg-color': '#0a0a0a',
        '--text-color': '#ffffff',
        '--accent-color': '#ff00ff',
        '--dark-bg': '#111111',
        '--card-bg': 'rgba(255, 255, 255, 0.05)'
    };

    // Function to apply theme
    const applyTheme = (theme) => {
        Object.entries(theme).forEach(([key, value]) => {
            htmlRoot.style.setProperty(key, value);
        });
    };

    // Initialize theme from localStorage
    const savedTheme = localStorage.getItem('site-theme') || 'dark';
    
    // Set initial theme
    htmlRoot.setAttribute('data-theme', savedTheme);
    applyTheme(savedTheme === 'light' ? lightTheme : darkTheme);

    // Theme toggle functionality
    themeToggle.addEventListener('click', () => {
        const currentTheme = htmlRoot.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        htmlRoot.setAttribute('data-theme', newTheme);
        applyTheme(newTheme === 'light' ? lightTheme : darkTheme);
        
        // Save preference to localStorage
        localStorage.setItem('site-theme', newTheme);

        // Update toggle icon
        themeToggle.innerHTML = newTheme === 'light' 
            ? '<i class="fas fa-moon"></i>' 
            : '<i class="fas fa-sun"></i>';
    });

    // Sync initial toggle icon
    themeToggle.innerHTML = savedTheme === 'light' 
        ? '<i class="fas fa-moon"></i>' 
        : '<i class="fas fa-sun"></i>';
});

// Optional: Ensure theme persistence across page loads
window.addEventListener('load', function() {
    const savedTheme = localStorage.getItem('site-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
});