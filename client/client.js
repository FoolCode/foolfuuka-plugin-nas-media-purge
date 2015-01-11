var fs = require('fs'),
  redis = require('redis'),
  client;

var processFile = function (path, file) {
  fs.rename(path + file, path + file + '.banned', function (err) {
    if (err) {
      console.log("\u2717 " + path + file);
    } else {
      console.log("\u2713 " + path + file);
    }
  });
}

fs.readFile('./config.json', { encoding: 'utf8' }, function (err, config) {
  if (err) {
    return console.error('Unable to load config.json.');
  }

  config = JSON.parse(config);
  client = redis.createClient(config.server.port, config.server.host);

  client.subscribe('foolfuuka:plugin:nas-media-purge');
  client.on('message', function (channel, file) {
    for (var i = 0, len = config.paths.length; i < len; i++) {
      processFile(config.paths[i], file);
    }
  });
});
