# How to contribute

## Questions and issues

If you have a question about or a general issue while using this library,
head over to the [discussions section](https://github.com/chillerlan/php-qrcode/discussions),
create a new post and describe your issue as precise as possible, ideally add a code example (if applicable).
Please don't just write "headline says all" because the reply will likely be similarly concise - help me (and others) help you!
(just to clarify: the "general" does not mean general PHP support, in which case you're better off on
[StackOverflow](https://stackoverflow.com/questions/tagged/php) or [/r/PHPhelp](https://www.reddit.com/r/PHPhelp/))


## Bug reports

So you found a bug or the library code is somehow misbehaving? That's great (well, not that great tho). In that case,
please [open a bug report and FILL OUT THE ISSUE TEMPLATE](https://github.com/chillerlan/php-qrcode/issues/new?assignees=&labels=bug&projects=&template=bug_report.md&title=%5BBUG%5D)
(i have to write that in all caps because nobody actually does it which usually leads to several avoidable follow-up questions that cost both of us precious time).
Below an example of the bug report template (it's not that hard):

**Describe the bug**

A clear and concise description of what the bug is.

**Steps to reproduce the behavior**
- When i do ...
- The code below ...
- Error message: ...

**Code sample**
```php
// your code here
```

**Expected behavior**

A clear and concise description of what you expected to happen.

**Screenshots**

If applicable, add screenshots to help explain your problem.

**Environment (please complete the following information):**
- PHP version/OS: [e.g. 7.4.12, Ubuntu 20.04]
- Library version: [e.g. 4.3.4]

**Additional context**

Add any other useful context about the problem.


## Pull requests and bug fixes

You want to contribute code to fix something or add a feature? Hey that's cool! However, there's a few things to keep in mind:

- Please add a description of what the PR does or fixes and why it should be merged. If you're unsure, [open an issue](https://github.com/chillerlan/php-qrcode/issues/new?assignees=&labels=enhancement&projects=&template=feature_request.md&title=%5BENHANCEMENT%5D) before to gather feedback.
- Make sure your branch is up-to-date/even with the upstream branch you're submitting your PR to.
- Please try to adhere to the [*loosely outlined* coding standards](https://github.com/chillerlan/php-qrcode/discussions/60), or, in case you're using [PHPStorm](https://www.jetbrains.com/phpstorm/), make sure you're using [the supplied IDE profile](https://github.com/chillerlan/php-qrcode/tree/main/.idea).


## Documentation

The documentation is a work in progress - any suggestion and contribution is very welcome!
If you have an addition or correction, feel fre to open a [documentation issue](https://github.com/chillerlan/php-qrcode/issues/new?assignees=&labels=docs&projects=&template=documentation.md&title=%5BDOCS%5D).


The API documentation is auto generated with [phpDocumentor](https://www.phpdoc.org/) from the docblocks [in the PHP sources](https://github.com/chillerlan/php-qrcode/tree/main/src).
The markdown sources for the [Read the Docs online manual](https://php-qrcode.readthedocs.io) are located in the [/docs directory](https://github.com/chillerlan/php-qrcode/tree/main/docs)
