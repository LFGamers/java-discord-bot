let config = require(__dirname + '/../config/config.json'),
    Bot    = require(__dirname + '/../src/js/Bot.js');

config.storageDetails = [
    {
        name:              'mongo',
        connectionString:  config.mongo_dsn,
        connectionOptions: {
            auto_reconnect: true,
            db:             {
                w: 1
            },
            server:         {
                socketOptions: {
                    keepAlive: 1
                }
            }
        },
        insertInto:        {
            collection: 'events'
        }
    },
    {
        name:              'redis',
        connectionString:  config.redis_dsn,
        connectionOptions: {},
        insertInto:        {
            channel: 'reactiflux-events'
        }
    }
];

return new Bot(config);
