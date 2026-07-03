<?php

namespace Tests\Feature;

use App\Exceptions\ExceptionRenderer;
use App\Jobs\SendCriticalErrorMail;
use App\Models\User;
use App\Services\CriticalErrorReporter;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Encryption\MissingAppKeyException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use PDOException;
use RuntimeException;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        Config::set('app.debug', false);

        Route::middleware('web')->get('/testing/server-error', function () {
            throw new RuntimeException('secret internal SQL: SELECT * FROM users');
        });

        Route::middleware('web')->get('/testing/query-error', function () {
            throw new QueryException(
                'sqlite',
                'select * from nonexistent_table',
                [],
                new PDOException('SQLSTATE[HY000]: General error: 1 no such table: nonexistent_table')
            );
        });
    }

    public function test_renderer_returns_csrf_json_response(): void
    {
        Config::set('app.debug', false);

        $renderer = app(ExceptionRenderer::class);
        $request = Request::create('/admin/notifications/read-all', 'POST');
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $renderer->render(new TokenMismatchException('CSRF token mismatch.'), $request);

        $this->assertNotNull($response);
        $this->assertSame(419, $response->getStatusCode());

        $payload = json_decode($response->getContent(), true);
        $this->assertFalse($payload['success']);
        $this->assertSame(__('errors.csrf_expired'), $payload['message']);
        $this->assertSame('csrf_expired', $payload['code']);
    }

    public function test_csrf_mismatch_on_form_post_redirects_back_with_inline_error(): void
    {
        Config::set('app.debug', false);

        $renderer = app(ExceptionRenderer::class);
        $request = Request::create(route('admin.login'), 'POST', [
            'email' => 'admin@example.com',
            'password' => 'secret',
        ]);
        $request->headers->set('referer', route('admin.login'));
        $this->startSession();
        $request->setLaravelSession($this->app['session.store']);

        $response = $renderer->render(new TokenMismatchException('CSRF token mismatch.'), $request);

        $this->assertNotNull($response);
        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('/admin/login', $response->headers->get('Location'));
        $this->assertSame('admin@example.com', session('_old_input.email'));
    }

    public function test_csrf_on_livewire_request_returns_json_not_redirect(): void
    {
        Config::set('app.debug', false);

        $renderer = app(ExceptionRenderer::class);
        $request = Request::create('/livewire/update', 'POST');
        $request->headers->set('X-Livewire', 'true');

        $response = $renderer->render(new TokenMismatchException('CSRF token mismatch.'), $request);

        $this->assertNotNull($response);
        $this->assertSame(419, $response->getStatusCode());
        $this->assertFalse($response->isRedirect());

        $payload = json_decode($response->getContent(), true);
        $this->assertFalse($payload['success']);
        $this->assertSame(__('errors.csrf_expired'), $payload['message']);
        $this->assertSame('csrf_expired', $payload['code']);
    }

    public function test_unauthenticated_admin_json_returns_friendly_message(): void
    {
        $response = $this->getJson(route('admin.notifications.index'));

        $response->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => __('errors.session_expired'),
                'code' => 'session_expired',
            ]);
    }

    public function test_forbidden_admin_json_returns_friendly_message(): void
    {
        $kitchen = User::factory()->create(['email_verified_at' => now()]);
        $kitchen->assignRole('Kitchen');

        $response = $this->actingAs($kitchen)
            ->postJson(route('admin.settings.test-pusher'));

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => __('errors.forbidden'),
                'code' => 'forbidden',
            ]);
    }

    public function test_storefront_not_found_renders_branded_page(): void
    {
        $response = $this->get('/this-page-does-not-exist-cakeshop');

        $response->assertNotFound();
        $response->assertSee('find that page', false);
        $this->assertStringNotContainsString('Stack trace', $response->getContent());
    }

    public function test_admin_not_found_renders_branded_page(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)
            ->get('/admin/this-route-does-not-exist');

        $response->assertNotFound();
        $response->assertSee('find that page', false);
    }

    public function test_json_server_error_hides_internal_details(): void
    {
        $response = $this->getJson('/testing/server-error');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => __('errors.server'),
            ]);

        $this->assertStringNotContainsString('secret internal SQL', $response->getContent());
    }

    public function test_json_query_exception_returns_database_message_without_sql(): void
    {
        $response = $this->getJson('/testing/query-error');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => __('errors.database'),
                'code' => 'database',
            ]);

        $content = $response->getContent();
        $this->assertStringNotContainsString('nonexistent_table', $content);
        $this->assertStringNotContainsString('SQLSTATE', $content);
    }

    public function test_rate_limited_notification_endpoint_returns_friendly_message(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $response = null;

        for ($i = 0; $i < 61; $i++) {
            $response = $this->actingAs($admin)
                ->getJson(route('admin.notifications.index'));
        }

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'message' => __('errors.too_many_requests'),
                'code' => 'too_many_requests',
            ]);
    }

    public function test_form_post_server_error_redirects_with_inline_message(): void
    {
        Config::set('app.debug', false);

        $renderer = app(ExceptionRenderer::class);
        $request = Request::create(route('admin.categories.store'), 'POST', [
            'name_en' => 'Test',
        ]);
        $request->headers->set('referer', route('admin.categories.create'));

        $response = $renderer->render(new RuntimeException('secret failure'), $request);

        $this->assertNotNull($response);
        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('categories/create', $response->headers->get('Location') ?? '');
    }

    public function test_query_exception_queues_critical_error_mail_in_production(): void
    {
        $this->enableCriticalErrorReporting();
        Queue::fake();

        $this->getJson('/testing/query-error')->assertStatus(500);

        Queue::assertPushed(SendCriticalErrorMail::class, 1);
    }

    public function test_server_error_queues_critical_error_mail_in_production(): void
    {
        $this->enableCriticalErrorReporting();
        Queue::fake();

        $this->getJson('/testing/server-error')->assertStatus(500);

        Queue::assertPushed(SendCriticalErrorMail::class, 1);
    }

    public function test_critical_error_mail_is_not_queued_for_csrf_mismatch(): void
    {
        $this->enableCriticalErrorReporting();
        Queue::fake();

        app(CriticalErrorReporter::class)->report(new TokenMismatchException('CSRF token mismatch.'));

        Queue::assertNothingPushed();
    }

    public function test_critical_error_mail_is_not_queued_for_authentication_exception(): void
    {
        $this->enableCriticalErrorReporting();
        Queue::fake();

        app(CriticalErrorReporter::class)->report(new AuthenticationException('Unauthenticated.'));

        Queue::assertNothingPushed();
    }

    public function test_critical_error_mail_is_not_queued_for_model_not_found(): void
    {
        $this->enableCriticalErrorReporting();
        Queue::fake();

        app(CriticalErrorReporter::class)->report(new ModelNotFoundException('Not found.'));

        Queue::assertNothingPushed();
    }

    public function test_critical_error_mail_is_not_queued_for_missing_app_key(): void
    {
        $this->enableCriticalErrorReporting();
        Queue::fake();

        app(CriticalErrorReporter::class)->report(new MissingAppKeyException('No application encryption key has been specified.'));

        Queue::assertNothingPushed();
    }

    public function test_critical_error_mail_is_not_queued_outside_production(): void
    {
        Config::set('app.env', 'local');
        config([
            'error-reporting.enabled' => false,
            'error-reporting.recipient' => 'ops@example.com',
        ]);
        Queue::fake();

        $this->getJson('/testing/server-error')->assertStatus(500);

        Queue::assertNothingPushed();
    }

    public function test_critical_error_mail_is_not_queued_without_recipient(): void
    {
        Config::set('app.env', 'production');
        config([
            'error-reporting.enabled' => true,
            'error-reporting.recipient' => null,
        ]);
        Queue::fake();

        $this->getJson('/testing/server-error')->assertStatus(500);

        Queue::assertNothingPushed();
    }

    public function test_identical_critical_errors_are_throttled(): void
    {
        $this->enableCriticalErrorReporting();
        Cache::flush();
        Queue::fake();

        $reporter = app(CriticalErrorReporter::class);
        $exception = new RuntimeException('duplicate failure');

        $request = Request::create('/testing/server-error', 'GET');
        $this->app->instance('request', $request);

        $reporter->report($exception);
        $reporter->report($exception);

        Queue::assertPushed(SendCriticalErrorMail::class, 1);
    }

    private function enableCriticalErrorReporting(): void
    {
        Config::set('app.env', 'production');
        config([
            'error-reporting.enabled' => true,
            'error-reporting.recipient' => 'ops@example.com',
            'error-reporting.throttle_minutes' => 15,
        ]);
    }
}
