<?php

namespace Drupal\Tests\lightning\ExistingSite;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\workflows\Entity\Workflow;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Ensures the integrity and correctness of Lightning's bundled config.
 *
 * @group lightning
 */
class ConfigIntegrityTest extends ExistingSiteBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // If the samlauth module is installed, ensure that it is configured (in
    // this case, using its own test data) to avoid errors when creating user
    // accounts in this test.
    if ($this->container->get('module_handler')->moduleExists('samlauth')) {
      $path = $this->container->get('extension.list.module')
        ->getPath('samlauth');
      $data = file_get_contents("$path/test_resources/samlauth.authentication.yml");
      $data = Yaml::decode($data);

      $this->container->get('config.factory')
        ->getEditable('samlauth.authentication')
        ->setData($data)
        ->save();
    }
  }

  /**
   * Tests configuration values were set correctly during installation.
   */
  public function testConfig() {
    $profile = $this->container->getParameter('install_profile');
    if ($profile !== 'lightning') {
      $this->markTestSkipped('This test can only be run against the Lightning install profile.');
    }

    $assert = $this->assertSession();

    // Assert that all install tasks have done what they should do.
    // @see lightning_install_tasks()
    $this->assertTrue(User::load(1)->hasRole('administrator'));

    $this->assertSame('/node', $this->config('system.site')->get('page.front'));
    $this->assertSame(UserInterface::REGISTER_ADMINISTRATORS_ONLY, $this->config('user.settings')->get('register'));
    $this->assertPermissions(Role::AUTHENTICATED_ID, 'access shortcuts');
    $this->assertSame('bartik', $this->config('system.theme')->get('default'));
    $this->assertSame('seven', $this->config('system.theme')->get('admin'));
    $this->assertTrue($this->config('node.settings')->get('use_admin_theme'));
    $this->assertContains('/lightning/lightning.png', $this->config('system.theme.global')->get('logo.path'));
    $this->assertContains('/lightning/favicon.ico', $this->config('system.theme.global')->get('favicon.path'));
    // TODO: Assert changes to the frontpage view were made.
    //
    // lightning_core_update_8002() marks a couple of core view modes as
    // internal.
    $view_modes = EntityViewMode::loadMultiple(['node.rss', 'node.search_index']);
    /** @var \Drupal\Core\Entity\EntityViewModeInterface $view_mode */
    foreach ($view_modes as $view_mode) {
      $this->assertTrue($view_mode->getThirdPartySetting('lightning_core', 'internal'));
    }

    // All users should be able to view media items.
    $this->assertPermissions('anonymous', 'view media');
    $this->assertPermissions('authenticated', 'view media');
    // Media creators can use bulk upload.
    $this->assertPermissions('media_creator', 'dropzone upload files');

    $this->assertEntityExists('node_type', [
      'page',
      'landing_page',
    ]);
    $this->assertEntityExists('user_role', [
      'landing_page_creator',
      'landing_page_reviewer',
      'layout_manager',
      'media_creator',
      'media_manager',
      'page_creator',
      'page_reviewer',
    ]);
    $this->assertEntityExists('crop_type', 'freeform');
    $this->assertEntityExists('image_style', 'crop_freeform');

    // Assert that the editorial workflow exists and has the review state and
    // transition.
    $workflow = Workflow::load('editorial');
    $this->assertInstanceOf(Workflow::class, $workflow);
    /** @var \Drupal\workflows\WorkflowTypeInterface $type_plugin */
    $type_plugin = $workflow->getTypePlugin();
    // getState() throws an exception if the state does not exist.
    $type_plugin->getState('review');
    // getTransition() throws an exception if the transition does not exist.
    /** @var \Drupal\workflows\TransitionInterface $transition */
    $transition = $type_plugin->getTransition('review');
    $this->assertEquals('review', $transition->to()->id());
    $from = array_keys($transition->from());
    $this->assertContainsAll(['draft', 'review'], $from);
    $this->assertNotContains('published', $from);

    $permissions = [
      'use text format rich_text',
      'access image_browser entity browser pages',
    ];
    $this->assertPermissions('page_creator', $permissions);
    $this->assertPermissions('landing_page_creator', $permissions);

    $node_types = \Drupal::entityQuery('node_type')->execute();

    $permissions = [
      'administer node display',
      'administer panelizer',
    ];
    // lightning_layout_update_8003() grants layout_manager role Panelizer
    // permissions for every node type.
    foreach ($node_types as $node_type) {
      $permissions[] = "administer panelizer node $node_type defaults";
    }
    $this->assertPermissions('layout_manager', $permissions);

    foreach ($node_types as $node_type) {
      $this->assertPermissions("{$node_type}_creator", [
        "create $node_type content",
        "edit own $node_type content",
        "view $node_type revisions",
        'view own unpublished content',
        'create url aliases',
        'access in-place editing',
        'access contextual links',
        'access toolbar',
      ]);
      $this->assertPermissions("{$node_type}_reviewer", [
        'access content overview',
        "edit any $node_type content",
        "delete any $node_type content",
      ]);
    }

    // Assert that bundled content types have meta tags enabled.
    $this->assertMetatag(['page', 'landing_page']);

    // Assert that basic blocks expose a Body field.
    $account = $this->createUser(['administer blocks']);
    $this->drupalLogin($account);
    $this->assertAllowed('/block/add/basic');
    $assert->fieldExists('Body');
    $this->drupalLogout();

    // Assert that Lightning configuration pages are accessible to users who
    // have an administrative role.
    $this->assertForbidden('/admin/config/system/lightning');
    $this->assertForbidden('/admin/config/system/lightning/api');
    $this->assertForbidden('/admin/config/system/lightning/layout');
    $this->assertForbidden('/admin/config/system/lightning/media');

    $account = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $this->assertAllowed('/admin/config/system/lightning');
    $assert->linkByHrefExists('/admin/config/system/lightning/api');
    $assert->linkByHrefExists('/admin/config/system/lightning/layout');
    $assert->linkByHrefExists('/admin/config/system/lightning/media');
    $this->assertAllowed('/admin/config/system/lightning/api');
    $this->assertAllowed('/admin/config/system/lightning/api/keys');
    $this->assertAllowed('/admin/config/system/lightning/layout');
    $this->assertAllowed('/admin/config/system/lightning/media');
  }

  /**
   * Asserts that meta tags are enabled for specific content types.
   *
   * @param string[] $node_types
   *   The node type IDs to check.
   */
  private function assertMetatag(array $node_types) {
    $assert = $this->assertSession();

    $permissions = array_map(
      function ($node_type) {
        return "create $node_type content";
      },
      $node_types
    );
    $account = $this->createUser($permissions);
    $this->drupalLogin($account);

    foreach ($node_types as $node_type) {
      $this->assertAllowed("/node/add/$node_type");
      $assert->fieldExists('field_meta_tags[0][basic][title]');
      $assert->fieldExists('field_meta_tags[0][basic][description]');
    }
    $this->drupalLogout();
  }

  /**
   * Asserts the existence of an entity.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param mixed|mixed[] $id
   *   The entity ID, or a set of IDs.
   */
  private function assertEntityExists($entity_type, $id) {
    $this->assertContainsAll(
      (array) $id,
      \Drupal::entityQuery($entity_type)->execute()
    );
  }

  /**
   * Asserts that a user role has a set of permissions.
   *
   * @param \Drupal\user\RoleInterface|string $role
   *   The user role, or its ID.
   * @param string|string[] $permissions
   *   The permission(s) to check.
   */
  private function assertPermissions($role, $permissions) {
    if (is_string($role)) {
      $role = Role::load($role);
    }
    $this->assertContainsAll((array) $permissions, $role->getPermissions());
  }

  /**
   * Asserts that a haystack contains a set of needles.
   *
   * @param mixed[] $needles
   *   The needles expected to be in the haystack.
   * @param mixed[] $haystack
   *   The haystack.
   */
  private function assertContainsAll(array $needles, array $haystack) {
    $diff = array_diff($needles, $haystack);
    $this->assertSame([], $diff);
  }

  /**
   * Asserts that the current user can access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  private function assertAllowed($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Asserts that the current user cannot access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  private function assertForbidden($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Returns an editable config object by name.
   *
   * @param string $name
   *   The name of the config object to return.
   *
   * @return \Drupal\Core\Config\Config
   *   The editable config object.
   */
  private function config($name) {
    return $this->container->get('config.factory')->getEditable($name);
  }

}
