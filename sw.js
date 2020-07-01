const staticCacheName = 'site-static-v2';

const dynamicCacheName = 'site-dynamic-v2';

const assets = [
    '/',
    'index.html',
    'src/scripts/app.js',
    'src/scripts/jquery.min.js',
    'src/scripts/bootstrap.min.js',
    'src/scripts/angular.min.js',
    'src/scripts/angular-locale_nl-nl.js',
    'src/scripts/angular-ui-router.min.js',
    'src/scripts/ngStorage.min.js',
    'src/scripts/main.js',
    'src/scripts/maincontroller.js',
    'src/scripts/dataservice.js',
    'src/scripts/userservice.js',
    'src/scripts/plannerservice.js',
    'src/scripts/agendacontroller.js',
    'src/scripts/settingcontroller.js',
    'src/scripts/logincontroller.js',
    'src/styles/bootstrap.min.css',
    'src/styles/main.css',
    'template/pages/agenda.html',
    'template/pages/settings.html',
    'template/pages/fallback.html',
    'template/pages/appointmentForm.html',
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
    evt.waitUntil(
        caches.keys().then(keys => {
            // console.log(keys);
            return Promise.all(keys
                .filter(key => key !== staticCacheName && key !== dynamicCacheName)
                .map(key => caches.delete(key))    
            )
        })
    );
});

// fetch event
self.addEventListener('fetch', (evt) => {
    //console.log('Fetch event: ', evt);
    evt.respondWith(
        caches.match(evt.request).then(cacheRes => {
            return cacheRes || fetch(evt.request).then(fetchRes => {
                return caches.open(dynamicCacheName).then(cache => {
                    cache.put(evt.request.url, fetchRes.clone());
                    return fetchRes;
                })
            });
        }).catch(() => {
            if(evt.request.url.indexOf('.html') > -1) {
                return caches.match('template/pages/fallback.html');
            }
        })
    );
});

function precache() {
    caches.open(staticCacheName).then(cache => {
        cache.addAll(assets);
    });
}
