Feature: Provide a push command sending translation to openl10n server

  Background:
    Given there is a file named ".openl10n.yml" with:
    """
    server:
        hostname: openl10n.server:
        username: user
        password : password

    project: my_project

    files:
        - trans/*.<locale>.yml
    """
    And there is a file named "trans/messages.en.yml" with:
    """
    foo: bar
    """
    And there is a openl10n server serving a "project" resource
    And there is a openl10n server serving a "resource" resource
    And "GET" call to "project" on "projects/my_project" will return 200 with:
    """
    {
    "slug": "my_project",
    "name": "my_project",
    "default_locale": "en",
    "description": "Project description"
    }
    """
    And "GET" call to "project" on "projects/my_project/languages" will return 200 with:
    """
    [
    {
        "locale": "en",
        "name": "english"
    },
    {
        "locale": "fr",
        "name": "french"
    }
    ]
    """

  Scenario: Push a new resource to server
    And "GET" call to "resource" on "resources?project=my_project" will return 200 with:
    """
    [
    ]
    """
    And "POST" call to "resource" on "resources" will return 201 with:
    """
    {
        "id": 53,
        "project": "my_project",
        "pathname": "trans/messages.en.yml"
    }
    """
    And "POST" call to "resource" on "resources/53/import" will return 204
    When I run "openl10n push --locale=all"
    Then it should pass with:
    """
    Creating resource trans/messages.en.yml
    Uploading file trans/messages.en.yml
    """
    And I should have "POST" call to "resource" on "resources" with:
    """
    {
    "project": "my_project",
    "pathname": "trans/messages.en.yml"
    }
    """
    And I should have "POST" call to "resource" on "resources/53/import"

  Scenario: Push an existing resource to server
    And "GET" call to "resource" on "resources?project=my_project" will return 200 with:
    """
    [
    {
        "id": 53,
        "project": "my_project",
        "pathname": "trans/messages.en.yml"
    }
    ]
    """
    And "POST" call to "resource" on "resources/53/import" will return 204
    When I run "openl10n push --locale=all"
    Then it should pass with:
    """
    Uploading file trans/messages.en.yml
    """
    And I should have "POST" call to "resource" on "resources/53/import"

  Scenario: Push a specific resource to server
    And there is a file named "trans/forms.en.yml" with:
    """
    foo: bar
    """
    And "GET" call to "resource" on "resources?project=my_project" will return 200 with:
    """
    [
    {
        "id": 53,
        "project": "my_project",
        "pathname": "trans/messages.en.yml"
    },
    {
        "id": 57,
        "project": "my_project",
        "pathname": "trans/forms.en.yml"
    }
    ]
    """
    And "POST" call to "resource" on "resources/53/import" will return 204
    When I run "openl10n push --locale=all trans/messages.en.yml"
    Then it should pass with:
    """
    Uploading file trans/messages.en.yml
    """
    And I should have "POST" call to "resource" on "resources/53/import"

  Scenario: Push various resources to server
    And there is a file named "trans/forms.en.yml" with:
    """
    foo: bar
    """
    And "GET" call to "resource" on "resources?project=my_project" will return 200 with:
    """
    [
    {
        "id": 53,
        "project": "my_project",
        "pathname": "trans/messages.en.yml"
    },
    {
        "id": 57,
        "project": "my_project",
        "pathname": "trans/forms.en.yml"
    }
    ]
    """
    And "POST" call to "resource" on "resources/57/import" will return 204
    And "POST" call to "resource" on "resources/53/import" will return 204
    When I run "openl10n push --locale=all"
    Then it should pass with:
    """
    Uploading file trans/forms.en.yml
    Uploading file trans/messages.en.yml
    """
    And I should have "POST" call to "resource" on "resources/57/import"
    And I should have "POST" call to "resource" on "resources/53/import"
