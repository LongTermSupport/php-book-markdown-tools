# Markdown Tools

A set of tools to assist with writing markdown and embedding code etc

Built specifically to assist with writing the PHP Book I have been working on

Currently this library provides the following functionality:

## Markdown Processing

This process is designed to update markdown files in place

### Code Snippets

Code snippets can be automatically included by specifying the path to the relevant PHP file

The code snippet can also be run, and the output captured and presented in another code block. 

To enable this, you must write your markdown in this specific style - whitespace sensitive:

#### Standard Code Snippet

The following will copy/paste the contents of the file into the code fence area. You must pre create the empty code
fence area. Each time you run the process, the code fence area will be updated with the code in the specified file.

The path is taken from the directory that the markdown file is located in.

~~~markdown
[Code Snippet](./../../../path/to/src/file.php)

```php

```
~~~

#### Executable Code Snippet

The executable snippet works in exactly the same way as the standard snippet, however it will also create an output 
block 

~~~markdown
[Code Executable Snippet](./../../../path/to/src/file.php)

```php

```
~~~

For example, the full code and output snippet will look like 

~~~markdown

[Code Executable Snippet](./itCanGetAndRunCodeSnippets.php)

```php
<?php 

$foo=1;
$bar=2;

function add(int $a, int $b):int{
    return $a+$b;
}echo "And new we add some stuff";
echo add($foo, $bar);
```

###### Output:
```terminal php itCanGetAndRunCodeSnippets.php

And new we add some stuff3

```

~~~

#### Github Code Snippet

Instead of referencing local code, you can also put a URL to a GitHub code page and embed that, for example:



The output and code snippet areas will be updated each time the process is run.

### Block Quote Processor

The next major piece of functionality is processing of block quotes. There are a few types of blockquote that are 
processed, though generally 
