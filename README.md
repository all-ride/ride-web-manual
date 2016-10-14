# Ride: Manual Web Integration

Manual browser for a Ride web application.

To activate, add the routes from _config/routes.manual.json_ to your configuration.

For example, in _application/config/routes.json_, you can set:

```json
{
    "routes": [
        {
            "path": "/admin/documentation/manual",
            "file": "config/routes.manual.json"
        },
    ]
}
```

## Related Modules

- [ride/app](https://github.com/all-ride/ride-app)
- [ride/app-api](https://github.com/all-ride/ride-app-api)
- [ride/app-markdown](https://github.com/all-ride/ride-app-markdown)
- [ride/lib-api](https://github.com/all-ride/ride-lib-api)
- [ride/lib-manual](https://github.com/all-ride/ride-lib-api)
- [ride/lib-http](https://github.com/all-ride/ride-lib-http)
- [ride/web](https://github.com/all-ride/ride-web)
- [ride/web-api](https://github.com/all-ride/ride-web-api)
- [ride/web-base](https://github.com/all-ride/ride-web-base)
- [ride/web-documentation](https://github.com/all-ride/ride-web-documentation)

## Installation

You can use [Composer](http://getcomposer.org) to install this application.

```
composer require ride/web-manual
```
