const staticCacheName = 'site-static';

const assets = [
    '/',
    'index.html',
    'src/scripts/app.js',
    'src/scripts/jquery.min.js',
    'src/scripts/bootstrap.min.js',
    'src/scripts/angular.min.js',
    'src/scripts/angular-locale_nl-nl.js',
    'src/scripts/angular-ui-router.min.js',
    'src/scripts/main.js',
    'src/scripts/controllers.js',
    'src/styles/bootstrap.min.css',
    'src/styles/main.css',
    'template/pages/agenda.html',
    'template/pages/settings.html',
    'src/images/icons/planner.svg',
    'src/images/icons/login.svg',
];

// install service worker
self.addEventListener('install', (evt) => {
    //console.log('Service worker has been installed.');
    evt.waitUntil(precache());
});

// activate event
self.addEventListener('activate', (evt) => {
    //console.log('Service worker has been activated.');
});

// fetch event
self.addEventListener('fetch', (evt) => {
    //console.log('Fetch event: ', evt);
});

function precache() {
    caches.open(staticCacheName).then(cache => {
        cache.addAll(assets);
    });
}