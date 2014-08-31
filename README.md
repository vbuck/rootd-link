# Rootd Links for Magento

Add enhanced URL rewrites to Magento (1.x) with a link management module.

This module was originally created as an exploration of the Magento routing system. I needed a practical example to understand how routers worked! With this module, you can:

* Create "pretty" URLs that point to other content on your site
* Create redirects to external URLs or attachments
* Protect your content with a password
* Set link availability (start and end active states)
* Dispatch custom Magento events when the link is hit
* Can automatically detect conflicts with pages using the same path

Why Do I Need This?
------
You don't :) You might then ask, "isn't this the same as the built-in URL rewrites, and don't CMS pages allow you to create custom paths?" Well, yes :) But it also goes a step further to add additional functionality that neither of those native modules offer.

How to Install
------
(modman file coming soon)

To install, clone or download the repository into your Magento base folder. A new module will be added to your **community** code pool, under *Rootd/Links*. Clear your cache. Then, you can create and manage links in Magento admin, under *CMS > Rootd Links*.

Please note that you **must enable** the features of this module in your System Configuration, under *Rootd Extensions > Rootd Links*. Otherwise your links will not be processed by Magento when requested.

Roadmap
------
Though built as a learning tool, I like using it! I'm planning to update it for the following functionality or features:

- [ ] Auto-generating paths, making the request path field optional
- [ ] Adding support for redirect (intermediate) pages
- [ ] Allowing the module frontName to be customized in admin

No dates for delivery given at this point.

License
------
Copyright (c) 2014 Rick Buczynski.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

