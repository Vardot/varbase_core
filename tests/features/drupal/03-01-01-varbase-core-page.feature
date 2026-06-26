@varbase_core @content
Feature: Varbase Core - basic page
  As a site administrator
  I want the Basic page add form to be reachable with Varbase Core enabled

  Scenario: The Basic page add form is reachable
    Given I am a logged in user with the "Webmaster" user
    When I open the administration page "/node/add/page"
    Then I should not see "Page not found"
