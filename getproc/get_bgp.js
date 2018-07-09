
/**
 * Set up page and script parameters.
 */
var page            = require('webpage').create(),
    system          = require('system'),
    find_str        = '',
    global_timeout  = 0,
    delay           = 0,
    response        = {},
    logs            = [],
    procedure       = {};

page.settings.userAgent = 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko';

/**
 * Add page errors to logs.
 */
page.onError = function (msg, trace) {

    var error = {
        message: msg,
        trace: []
    };

    trace.forEach(function(t) {
        error.trace.push((t.file || t.sourceURL) + ': ' + t.line + (t.function ? ' (in function ' + t.function + ')' : ''));
    });

    logs.push(error);
};

/**
 * Global error handling.
 */
phantom.onError = function(msg, trace) {

    var stack = [];

    trace.forEach(function(t) {
        stack.push((t.file || t.sourceURL) + ': ' + t.line + (t.function ? ' (in function ' + t.function + ')' : ''));
    });

    response.status  = 500;
    response.content = msg;
    response.console = stack;

    console.log(JSON.stringify(response, undefined, 4));
    phantom.exit(1);
};

if ( system.args.length !== 4 ){
    throw new Error("No enough parameters.");
}

    find_str = system.args[1];
    global_timeout = system.args[2];
    delay = system.args[3];

//throw new Error(crawle_url+'/'+global_timeout+'/'+delay);

page.settings.resourceTimeout = (global_timeout * 1000);

/**
 * Set error in response on timeout.
 */
page.onResourceTimeout = function (e) {
    response        = e;
    response.status = e.errorCode;
};

/**
 * Set response from resource.
 */
page.onResourceReceived = function (r) {
    if(!response.status) response = r;
};

/**
 * Set timeout.
 */
window.setTimeout(function () {
    phantom.exit();
}, (global_timeout * 1000));

/**
 * Open page.
 *
 * @param string $url
 * @param string $method
 * @param string $parameters
 * @param callable $callback
 */
//page.open ('http://bgp.he.net/search?search%5Bsearch%5D=%22SoftLayer+Technologies+Inc.%22&commit=Search', 'GET', '', function (status) {

page.open ('http://bgp.he.net/search?search%5Bsearch%5D='+find_str+'&commit=Search', 'GET', '', function (status) {
    if (status !== 'success') page.render('page.png');
});

/**
 * Delay grabbing page that redirect page
 */

if(!delay) {
    procedure.execute();
}
else {
    window.setTimeout(function () {
        procedure.execute();
    }, (delay * 1000));
	
	window.setInterval(function () {
		var res = page.evaluate(function() {
			result = 'false';

			if(document.getElementById('search')){
				result = 'true';
			}

			return result;
		});

		if(res == 'true'){
            page.onLoadFinished = procedure.execute();
		}
    }, (1000));
}

/**
 * Command to execute on page load.
 */
procedure.execute = function () {

    try {

        //response.content = page.evaluate(function () {
            //return document.getElementsByTagName('html')[0].innerHTML
            //return page.content
        //});
        response.content = page.content;
    } catch(e) {

        response.status  = 500;
        response.content = e.message;
    }

    response.console = logs;
    console.log(JSON.stringify(response, undefined, 4));

    phantom.exit();
};

