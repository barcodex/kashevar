# kashevar

Kashevar is a generator for web apps built on top of Kasha framework.

It provides basic scaffolding on the same principles as Rails or Symfony.
It is far from being on the same level of feature completeness, but it does what it is made for.

Even though it builds Kasha apps, it is not dependent on Kasha framework, the only dependencies you will find in composer.json, is PhpUnit to run tests.

## What's the strange name?

'Kasha' means 'porridge' in Russian. The person who prepares a porridge, is called 'kashevar'.

So, suddenly it is quite logical when kashevar prepares some kasha.

## Directory structure of generated applications

The overall folder structure of Kasha application looks like this:

```
/
/app
/app/cache
/app/modules
/app/modules.translation
/vendor/
/web
/web/assets
```

Root folder contains single files, too - these are mostly all kinds of config files, many of which should not be kept under version control.

The folder 'app' and its content are there for storing all the application files - controller code, model classes and the views - organised into modules. Since views are expected to be in different languages, folder 'modules.translation' is there to store all the translations for the views. Finally, 'cache' is there to store all kind of items that framework reuses between the requests - in case if filesystem-based caching is used.

Kasha framework is fully Composer-compatible, so 'vendor' folder contains all framework components as well as any dependency that kasha itself or kasha-based app might have. Logically enough, 'composer.json' files is expected to be in the root folder.

When kashevar is used to generate the applications, it also creates composer.json with kasha dependencies, so quick ```composer update``` in the root folder of generated application will already bring in all the dependencies that kasha has.

Kasha-based applications also have an extra autoloading next to the one supplier by composer, which always searches for the classes inside of folders under 'app/modules' directory. This allows us the app creator to introduce and use new classes without updating composer each time when new classes appear.

Finally, modules (closest child directories of '/app/modules') also have an expected structure:

```
/
/actions
/sql
/templates
/dict
/classes
```

Controller code is stored in "action" files under actions. Unlike many other frameworks, kasha does not use classes for defining controllers - this is done for quick finding the code by the file name (thanks to naming conventions) whenever route is known.

Views are stored in the files that always have .html extension and are located under 'templates'.

Model classes are expected to be found in 'classes' folder. They are all extending Kasha\Model\Model class, which provides some useful abstractions for working with all kinds of models.

By design, kasha is not too flexible in regard of database access - even if Model class provides some handy abstractions, in some cases simple queries are not enough and custom SQL code is needed. ORM-based framework invent their own query language to do that, but kasha goes the simple way and allows to write database-specific queries natively, sacrificing on the flexibility of changing the backends (and the idea is that in 95% of cases databases are never changed during the lifecycle of the system).

## Generating process

Recommended way to scaffold a kasha app is to open a Terminal window, change to kashevar folder and type a command (for the sake of example, let's assume that app name is "blog"):

```bash
php generate.php app:create blog
```

You will then be asked some questions in interactive mode. Generator needs this info to prepare basic configuration and skeletons of the app itself.


