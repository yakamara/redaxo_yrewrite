redaxo4_yrewrite
================

A multidomain URL rewrite engine for REDAXO4

Edit config.inc.php for your setup

Setup the different Domains.
rex_yrewrite::setDomain(domain, mount_id, start_id, 404_id);

domain = plain domain name: "mydomain.de"
mount_id = REDAXO Category-ID where everything starts.
start_id = Start Article of domain. Is in the mount_id category. Normally the first category inside moun_id
404_id = If article is not found in this domain, this article will be called.

Setup existing alias domains
rex_yrewrite::setAliasDomain(from_domain, to_domain);

Example:

rex_yrewrite::setDomain("mydomain.de", 1, 10, 20);
rex_yrewrite::setDomain("mydomain1.de", 3, 13, 23);
rex_yrewrite::setDomain("mydomain2.de", 5, 15, 25);

// Alias Domains
rex_yrewrite::setAliasDomain("www.mydomain.de", "mydomain.de");
rex_yrewrite::setAliasDomain("www.mydomain1.de", "mydomain1.de");
rex_yrewrite::setAliasDomain("www.mydomain2.de", "mydomain2.de");


