const Eris   = require("eris"),
      moment = require('moment'),
      colors = require('colors'),
      pipend = require('pipend-spy');

class Bot {
    constructor(config) {
        this.config = config;
        this.bot    = new Eris(config.token, {getAllUsers: true});
        this.record = pipend(config.storageDetails).record;

        this.bot.on("ready", e => {
            this.log('green', "Connected to discord");
        });
        //this.bot.on('debug', this.log.bind(this, 'grey'));

        this.bot.on('messageCreate', this.onMessage.bind(this));
        this.bot.on('messageUpdate', this.onMessageUpdate.bind(this));
        this.bot.on('presenceUpdate', this.onPresenceUpdate.bind(this));
        this.bot.on('guildMemberAdd', this.onMemberAction.bind(this, 'join'));
        this.bot.on('guildMemberRemove', this.onMemberAction.bind(this, 'remove'));

        this.bot.connect();
    }

    onMemberAction(type, guild, member) {
        if (!this.isLoggedServer(guild)) {
            return;
        }

        this.recordEvent(
            member,
            (new Date()).getTime(),
            type === 'join' ? 'new-user' : 'left-community',
            {}
        );
    }

    recordEvent(member, date, type, data) {
        let args = {
            username:     member.user.username,
            "user-id":    member.user.id,
            timestamp:    date,
            "event-type": type,
            "event-data": data
        };
        this.log('gray', 'record ' + this.pretty(args));

        let joinedAt          = member.joinedAt,
            joinedAtTimestamp = !!joinedAt ? (new Date(joinedAt)).getTime() : 0;

        data.timestamp     = date;
        data['time-delta'] = date - joinedAtTimestamp;
        data['user-id']    = member.user.id;
        data.username      = member.user.username;

        let result = this.record({
            "event-type": type,
            "event-args": data
        });

        result
            .then(() => this.log('green', 'success, ' + this.pretty(args)))
            .catch(err => this.log('red', `error, ${this.pretty(args)}, ${err}`));
    }

    pretty(obj) {
        return JSON.stringify(obj, null, 4);
    }

    log(color, message) {
        if (this.config.log_js_events) {
            let currentTime = moment().format('ddd, D MMM YYYY, hh:mm:ss a');
            console.log(colors[color](`[${currentTime}] ${message}`));
        }
    }

    onMessage(message) {
        if (!this.isLoggedChannel(message.channel.guild, message.channel)) {
            return;
        }

        this.recordEvent(
            message.member,
            (new Date()).getTime(),
            "message",
            {
                "channel-name":     message.channel.name,
                "mentions":         message.mentions.map(id => this.bot.users.find(user => user.id === id).username),
                "mention-everyone": message.mentionedBy.everyone,
                "message-id":       message.id,
                "message":          message.cleanContent
            }
        );
    }

    onMessageUpdate(message, oldMessage) {
        if (!this.isLoggedChannel(message.channel.guild, message.channel)) {
            return;
        }

        this.recordEvent(
            message.member,
            (new Date()).getTime(),
            "messageUpdate",
            {
                "channel-name":     message.channel.name,
                "mentions":         message.mentions.map(id => this.bot.users.find(user => user.id === id).username),
                "mention-everyone": message.mentionedBy.everyone,
                "message-id":       message.id,
                "message":          message.cleanContent
            }
        );
    }

    onPresenceUpdate(member, oldPresence) {
        if (!this.isLoggedServer(member.guild)) {
            return;
        }

        this.recordEvent(
            member,
            (new Date()).getTime(),
            "presence",
            {
                "status":    member.status,
                "game-name": member.game ? member.game.name : null,
                "game-type": member.game ? member.game.type : null,
                "game-url":  member.game ? member.game.url : null
            }
        )
    }

    isLoggedServer(server) {
        if (undefined === server) {
            return false;
        }
        this.log('grey', `Checking to see if ${server.id} is logged`);

        for (let s of this.config.logged_servers) {
            if (server.id === s.id) {
                return true;
            }
        }

        return false;
    }

    isLoggedChannel(server, channel) {
        if (!this.isLoggedServer(server)) {
            return false;
        }
        this.log('grey', `Checking to see if ${server.id} + #${channel.id} is logged`);

        for (let s of this.config.logged_servers) {
            if (server.id === s.id) {
                return s.ignored_channels.indexOf(channel.id) < 0;
            }
        }

        return false;
    }
}

module.exports = Bot;
