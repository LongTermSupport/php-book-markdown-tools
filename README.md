# Markdown Tools

A set of tools to assist with writing markdown and embedding code etc

Built specifically to assist with writing the PHP Book

### Wikipedia Links

You can specify Wikipedia links as block quotes such as

```markdown
> https://en.wikipedia.org/wiki/Covariance_and_contravariance_(computer_science)
```

or if you want to use text highlighting (which is very hit and miss in the browser, seems a Chrome feature and not 
reliable), then it 
must be a 
version URL 
such as and then the text you want to highlight and also quote is with the special `#:~:text=` marker. I tend to 
just copy/paste the text into the address bar and let chrome do the url encoding for me. This text will be quoted in 
the resultant blockquote
```markdown
> https://en.wikipedia.org/w/index.php?title=Covariance_and_contravariance_(computer_science)&oldid=1001839343#:~:text=In%20the%20OCaml%20programming%20language,%20for%20example,%20%22list%20of%20Cat%22%20is%20a%20subtype%20of%20%22list%20of%20Animal%22%20because%20the%20list%20type%20constructor%20is%20covariant.%20This%20means%20that%20the%20subtyping%20relation%20of%20the%20simple%20types%20are%20preserved%20for%20the%20complex%20types.%20On%20the%20other%20hand,%20%22function%20from%20Animal%20to%20String%22%20is%20a%20subtype%20of%20%22function%20from%20Cat%20to%20String%22%20because%20the%20function%20type%20constructor%20is%20contravariant%20in%20the%20parameter%20type.
```