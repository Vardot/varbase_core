@varbase_core @admin
Feature: Varbase Core - administration pages
  As a site administrator
  I want the core administration pages to be reachable with Varbase Core and all
  of its submodules enabled

  Scenario: The core administration pages are reachable for the administrator
    Given I am a logged in user with the "Webmaster" user
    When I open the administration page "/admin/content"
    Then I should not see "Page not found"
    When I open the administration page "/admin/config"
    Then I should not see "Page not found"
    When I open the administration page "/admin/structure"
    Then I should not see "Page not found"
    When I open the administration page "/admin/people"
    Then I should not see "Page not found"
    When I open the administration page "/admin/reports/status"
    Then I should not see "Page not found"
