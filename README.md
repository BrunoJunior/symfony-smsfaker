Symfony SMS Faker (forked from Fake SMS Notifier)
=================

Provides Fake SMS (as email or log during development) integration for Symfony Notifier.
This project add the content of the notification in the send e-mail (or in the log).
The symfony original notifier only send the subject …

#### DSN example for email

```
SMS_FAKER_DSN=smsfaker+email://default?to=TO&from=FROM
```

where:

- `TO` is email who receive SMS during development
- `FROM` is email who send SMS during development

WARNING : `TO` and `FROM` has to be urlencoded (ex: @ => %40)

To use a custom mailer transport:

```
SMS_FAKER_DSN=smsfaker+email://mailchimp?to=TO&from=FROM
```

#### DSN example for logger

```
SMS_FAKER_DSN=smsfaker+logger://default
```

Resources
---------

* [Original project](https://github.com/symfony/symfony/tree/7.1/src/Symfony/Component/Notifier/Bridge/FakeSms)
