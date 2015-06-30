Behat BDD Framework
======================================================

Behat provides tools for writing and executing BDD tests. Specifically, Behat will read user stories written in Gherkin and generate skeleton PHP methods for our tests. Once we implement these methods, we can run Behat again and it will execute the tests.

There are two types of tests that we are managing with Behat: functional and integration. Functional tests are a type of black box test for a slice of functionality across a system. Behat integrates with Selenium to perform functional tests through the Chrome web browser. Integration tests verify that our code works properly with other modules within Magento. These tests are performed by bootstrapping the relevant parts of the application and inspecting the state of PHP objects using PHP code.

## Test Organization

Behat has a number of abstractions for organizing tests. This section describes how we are using profiles to separate different types of tests (functional, integration), and suites to group related features (e.g. a Magento module that has multiple features).

The Behat configuration file `/behat.yml` defines multiple profiles for testing features. The default profile defines the location of the feature files, which are grouped into test suites. For each suite, there should be a subdirectory containing all of the related feature files at `/features/<suite-name>/`. A suite roughly corresponds to one Magento module, and serves as a way of grouping multiple features together. For smaller modules, the suite may have only one feature.

The functional and integration profiles do not need to define which features they test, since by convention they share the same set. For example, each address validation scenario will be tested at the functional level, as well as at the integration level. Although they share the same features, by dividing the two types of tests into separate profiles, it is possible to execute them separately, and to define different methods of testing (eg. examining the state of PHP objects, versus reading the browser screen with a web driver).

Each profile defines the namespace of the context it will use for testing. Context files are PHP files that are located in `/tests/<profile-name>/EbayEnterprise/<suite-name>/`. The context defines exactly how the features will be tested.

## Feature Files

To create a new feature, a `*.feature` file has to be created in the `/features/<suite-name>` directory. If the feature is for a new Magento module, then a new suite subdirectory will have to be created and added to `behat.yml`.

## Best Practices

* Backgrounds should be used to prepare each test (eg. creating a customer account and logging in).
* Scenario outlines should be used to run a test with different sets of arguments.
* Each scenario should be written independently. State is not preserved between scenarios.
* Arguments should ALWAYS be quoted in step definitions.
* Arguments should NEVER be quoted in example tables.

## Context Files

Each line of a scenario (a "step") is mapped to one method in a context file (a "step definition"). This mapping is performed by comparing the text of the step to the phpDoc annotation above each method. The annotation is a regular expression that can flexibly match different wordings of the same basic step, so you might have multiple steps in different scenarios that map to a single step defintion. If the expression contains any capture groups, they will mark the location of the method arguments in the step.

By default, Behat provides a simplified syntax in place of regular expressions, which it uses when it initially generates PHP code (called "snippets"). Any words or numbers that are quoted in the step definition will be interpreted as method arguments. Instead of using capture groups in the phpDoc, each word that was quoted should be preceded by a colon:

..example..

## Additional Links

Behat
http://docs.behat.org/en/v2.5/ Documentation for the previous version of Behat
http://docs.behat.org/en/latest/ Current version, but incomplete

Mink
http://mink.behat.org/en/latest/

Gherkin
https://github.com/cucumber/cucumber/wiki/Gherkin
