#Smart Pieces

Simple framework to build smart pieces of code and generate them from the command line.

##Table of Contents

1. [What is Smart Pieces ?](#what-is-smart-pieces)
2. [Install Smart Pieces](#install-smart-pieces)
3. [Getting Started](#getting-started)
4. [Format API](#format-api)
5. [What is Next ?](#what-is-next)

##What is Smart Pieces

Smart Pieces is a new way to define and use code snippets.

###What is the problem of normal snippets ?

Snippets are really useful while writing code; Instead of writing all the code, you just hit some keys and the snippet is written automatically by your editor. I am using Sublime Text and I started defining snippets for every piece of code I usually use. But every time I insert a snippet, I have to fill in the gaps and remove some optional parts, and it starts to be more stupid when I have to fill two place holders while the second one is just a CamlCase of the first. More then that, there is no `include` statement in snippets ( You can't include one snippet into an other ).

###The solution ?

Instead of inserting the snippet then filling the place holders, you pass the values of placeholder while calling the snippet, the snippet becomes a command accepting values as parameters. You can also specify flags to show/hide optional parts. Smat Pieces are written using the [Twig templating engine](http://twig.sensiolabs.org/), So filters are there and you don't have to pass the same value twice if a filter can get the second value from the first one. Alternatives, Loops and include statement are also available.

##Install Smart Pieces

1. Install `php` and `composer` if you don't have them already
2. Clone this repository locally or download it as a zip file and extract in some location
3. Run `composer install` to install dependencies
4. Make sure the file `smart-pieces` is executable `chmod +x smart-pieces`
5. Run it, `./smart-pieces` if you see the help screen so all right !

##Getting Started

Now that you installed Smart Pieces, let's build something useful !

###Add Your First Snippet

Let's start by adding a new snippet for the piece of code we use to test if an array contains an element, and to make it easy to remember we call it `contains`. First we create a folder with name `contains` under the `snippets` directory and we add a file `default.twig` into it:

    snippets
      |-- contains
          |-- default.twig

`default.twig` contains our snippet default template. The parameters passed to the snippet will be automatically available in the template as an array `data`. In our simple snippet, we just need the container name and the element we are searching for in order to build the piece of code. So our template could be like this (for PHP language):

    if( in_array(${{data[1]}}, ${{data[0]}}) ){
       
    }

In this template, we assume that the first parameter (`data[0]`) is the container and the second (`data[1]`) is the element we are checking. To see the result, run this command:

`./smart-pieces render contains myArray myElement`

It should get this as output:

    if( in_array($myElement, $myArray) ){
       
    }

Simple right ? But Smart Pieces are smarter than that !

###Define Your First Format

What if we need to do multiple checks ? A first solution could be to pass parameters like this:

`./smart-pieces render contains myArray1 myElement1 myArray2 myElement2 ...`

But this would be a bad solution because it will make the template complex to write. A good solution would be:

`./smart-pieces render contains myArray1:myElement1 myArray2:myElement2 ...`

"Huh ?! What is the difference ?". The difference is that now `myArray1:myElement1` a single parameter, and don't worry, you will not have to split it in the template. Smart Piece will do it for you. You just need to describe how do you want it to be splitted by adding an new file "format.json" into the `contains` directory. The content of this file will be:

    {
        "type": "object",
        "fields": {
            "list": "string",
            "element": "string"
        }
    }

Here we are saying that every parameter is an object with two string fields: list and element. Note that we declared the fields in the same order as in the command line. Now our template becomes:

    if( in_array(${{data[0].element}}, ${{data[0].list}}){% for c in data|slice(1) %} && in_array(${{c.element}}, ${{c.list}}){% endfor %} ){
       
    }

Now the command `./smart-pieces render contains myArray1:myElement1 myArray2:myElement2` should output:

    if( in_array($myElement1, $myArray1) && in_array($myElement2, $myArray2) ){
       
    }

###Add Specific Templates

Our snippet is working now, but only for PHP language. Instead of making other snippets for other languages, you can just add other templates in the `contains` directory with the name `language.twig`. Let's add the Javascript version for example:

    if( {{data[0].list}}.indexOf({{data[0].element}}) != -1{% for c in data|slice(1) %} && {{c.list}}.indexOf({{c.element}}) != -1{% endfor %} ){
       
    }

We save this template with the name `js.twig` and we run this command:

`./smart-pieces render js:contains myArray1:myElement1 myArray2:myElement2`

Please note the `js:` before the snippet name, this is how we specify the template.

##Format API

###Attributes

the `format.json` file is used to describe the structure of a parameter. A format has the following attributes:

- `type`: could be `object` or `array`
- `separator`(optional): the elements separator; default values are `:` for an object and `,` for an array.
- `format`(for an array): the format of an element of the array
- `fields`(for an object): an object representing the fields, where each attribute is the name of a field and the value is the format of that field. If the field is a simple string, `"string"` could be used as value.
- `flags`(for an object): array of flag associated to this object; a flag is a simple string.

###Example

`format.json`:

    {
        "type": "object",
        "fields": {
            "name": "string",
            "return": "string",
            "args": {
                "type": "array",
                "format": {
                    "type": "object",
                    "separator": ".",
                    "fields": {
                        "name": "string",
                        "type": "string"
                    }
                }
            }
        },
        "flags": [ "static", "inline" ]
    }

The command: `./smart-pieces render snippet-name sum:int:a.int,b.int:static`

The data sent to the template:

    [
      0 => [
        name => 'sum',
        return => 'int',
        args => [
          0 => [
            name => 'a',
            type => 'int'
          ],
          1 => [
            name => 'b',
            type => 'int'
          ]
        ],
        static => true,
        inline => false
      ]
    ]

##What is Next

Smart Pieces is actually working from the command line, but should be able to call it directly from within your favourite editor. That's why I am working on plugins for major editors. In particular, the ST3 plugin will be released soon.

Please report any bugs or issues you find. Pull requests are welcome, I will be adding the most used snippets and you can contribute with yours.
