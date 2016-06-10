# Environment (and Variables for Configuration)

- we're using php-dotenv with a .env file for configuration of a project
- leave the parameters.yml empty
- set `SYMFONY_ENV` to `prod` or `test` or `dev` or `staging`
- set `SYMFONY_DEBUG` to 0 or 1 to toggle debug mode

You can set parameters for symfony (like you would int he parameters.yml) with these rules:

1. SYMFONY__ prefix is removed;
2. Parameter name is lowercased;
3. Double underscores are replaced with a period, as a period is not a valid character in an environment variable name.

e.g. `SYMFONY__DATABASE_PASSWORD` will be like setting `database_password` in the parameters.yml or `SYMFONY__ROUTER__REQUEST_CONTEXT__HOST` should be `router.request_context.host`.

For production set the `SYMFONY_ENV` to `prod` and provide all variables as environment parameters. If this isn't possible write them to the parameters.yml. Don't use a .env file in production environments, because it would be parsed on every request. The `parameters.yml` file is cached from symfony.
Try to avoid passing special env variables in apache virtual hosts with SetEnv to your application, because it won't work in cli.

## Why don't we use parameters.yml for all of this?

Because you cannot put the symfony environment (`prod` or `dev`) for the kernel in to the parameters.yml. The kernel is already constructed, when the parameters.yml is read.  
If we would migrate to docker we can pass all env variables from the container and won't need to write additional .env files or something.

## Don't store parameters in the apache

This is the root of all evil, because your parameters won't be the same in CLI.

## Why should we use / not use php-dotenv?

We have several requirements for our configuration system:

 - passwords and sensible data shouldn't be checked into the VCS
 - configuration shouldn't be duplicated for apache and CLI
 - .env shouldn't be used in production because it's not cached (and many say so not to do)
 - the environment is not global for one OS (imagine production + staging server on the same machine)
 - be as compatible as possible with the symfony-standard edition

The twelve-factor app says: pass everything from environment-variables, but this is only really capable if you have the full control over all environment variables. Especially if you have several projects in one OS-Environment you're somehow screwed.

So basically we have to write the configuration for some environment onto a readable file that is loaded from apache and cli. This was the symfony parameters.yml and worked well. YAML is easy to write for continuous integration servers, its transparent and easy to debug AND the information is cached from symfony and won't be read on every request.

IF we use .env for development and just environment variables for production we dont know where  to set the environment variables. Apache is not allowed (duplication) and OS-Environment-Variables might be not settable - and will conflict with several projects.


# Conclusion

We NEED to write a file next to the code that gets read by apache AND cli that finds out the environment and debug mode. With this environment and debug mode the kernel is created. The other parameters are then extracted from the parameters.yml.

This will keep the overhead for reading a file on every request, very very low. Because only 2 variables are read from the .env file. We can still set those two parameters with SetEnv from Apache in the virtualHost but we have to keep in mind then, that the cli-environment might be out-of-sync with the apache one.

For Multi-Backend-Systems (like IWEP) we have to find a solution other than storing the credentials in the virtual-host conf (anyway).

This seems to be semantically correct: .env loads information about the SYMFONY-ENVIRONMENT. The Symfony-Environment contains parameters for symfony (in parameters.yml). Both files can be written from deployment servers