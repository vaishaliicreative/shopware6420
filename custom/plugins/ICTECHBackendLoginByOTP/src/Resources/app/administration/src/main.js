import enGB from './snippet/en-GB.json';
import deDE from './snippet/de-DE.json';

import './module/sw-login';
// import './module/sw-login/view/sw-login-verify'

Shopware.Locale.extend('en-GB', enGB);
Shopware.Locale.extend('de-DE', deDE);
console.log("call main js")

// Shopware.Module.register('sw-login-verify-route', {
//     routeMiddleware(next, currentRoute) {
//         // console.log(currentRoute.name);
//         // if (currentRoute.name === 'sw.login.index') {
//         //     currentRoute.children.push({
//         //         path: '/login/verify',
//         //         coreRoute: true,
//         //         component: 'sw-login-verify',
//         //         name: 'sw.login.index.verify',
//         //         meta: {
//         //             parentPath: "sw.login.index"
//         //         }
//         //     });
//         // }
//         console.log(currentRoute);
//         // next(currentRoute)
//     }
// });
