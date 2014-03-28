# Ride: Manual Web Integration

Manual browser for a Ride web application.

To activate, add the routes from _config/routes.manual.json_ to your configuration.

For example, in _application/config/routes.json_, you can set:

    {
        "routes": [
            {
                "path": "/admin/documentation/manual",
                "file": "config/routes.manual.json"
            },
        ]
    }
