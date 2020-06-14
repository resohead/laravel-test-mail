<?php

namespace Resohead\LaravelTestMail\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Mail;
use Resohead\LaravelTestMail\TestMailable;
use Illuminate\Support\Facades\Artisan;
use Resohead\LaravelTestMail\LaravelTestMailServiceProvider;

class TestMailTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelTestMailServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->defaultEmailAddress = 'recipient@example.com';
        $app['config']->set('mail.from.address', $this->defaultEmailAddress);
        $app['config']->set('mail.driver', 'smptp');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = 'mail:test';
        $this->emailAddress = 'recipient@example.com';

        Mail::fake();
    }

    /** @test */
    public function it_can_be_sent_without_any_arguments()
    {
        $exitCode = Artisan::call($this->command);
        $this->assertStringContainsString($this->defaultEmailAddress, Artisan::output());
        $this->assertEquals(0, $exitCode);

        Mail::assertSent(TestMailable::class, function (TestMailable $mail) {
            $mail->build();
            $this->assertCount(1, $mail->to);
            $this->assertEquals($this->emailAddress, $mail->to[0]['address']);
            $this->assertCount(0, $mail->cc);
            $this->assertCount(0, $mail->bcc);

            return true;
        });
    }

    /** @test */
    public function it_can_be_sent_synchronously_to_an_email()
    {
        $exitCode = Artisan::call($this->command,[
            'recipient' => $this->emailAddress
        ]);

        $this->assertStringContainsString($this->emailAddress, Artisan::output());
        $this->assertEquals(0, $exitCode);

        Mail::assertSent(TestMailable::class, function (TestMailable $mail) {
            $mail->build();
            $this->assertCount(1, $mail->to);
            $this->assertEquals($this->emailAddress, $mail->to[0]['address']);
            $this->assertCount(0, $mail->cc);
            $this->assertCount(0, $mail->bcc);

            return true;
        });
    }


    /** @test */
    public function it_is_queued_with_the_queue_flag_only()
    {
        $exitCode = Artisan::call($this->command,[
            'recipient' => $this->emailAddress,
            '--queue' => null
        ]);

        $this->assertStringContainsString($this->emailAddress, Artisan::output());
        $this->assertEquals(0, $exitCode);

        Mail::assertQueued(TestMailable::class, function (TestMailable $mail) {
            $mail->build();
            $this->assertCount(1, $mail->to);
            $this->assertEquals($this->emailAddress, $mail->to[0]['address']);
            $this->assertCount(0, $mail->cc);
            $this->assertCount(0, $mail->bcc);

            return true;
        });
    }

    /** @test */
    public function it_is_queued_with_stack_only()
    {
        $exitCode = Artisan::call($this->command,[
            '--stack' => 'emails'
        ]);

        $this->assertStringContainsString($this->emailAddress, Artisan::output());
        $this->assertEquals(0, $exitCode);

        Mail::assertQueued(TestMailable::class, function (TestMailable $mail) {
            $mail->build();
            $this->assertEquals('emails', $mail->queue);
            $this->assertCount(1, $mail->to);
            $this->assertEquals($this->emailAddress, $mail->to[0]['address']);
            $this->assertCount(0, $mail->cc);
            $this->assertCount(0, $mail->bcc);

            return true;
        });
    }

    /** @test */
    public function it_is_queued_with_connection_only()
    {
        $exitCode = Artisan::call($this->command,[
            '--connection' => 'sync'
        ]);

        $this->assertStringContainsString($this->emailAddress, Artisan::output());
        $this->assertEquals(0, $exitCode);

        Mail::assertQueued(TestMailable::class, function (TestMailable $mail) {
            $mail->build();
            $this->assertEquals('sync', $mail->connection);
            $this->assertCount(1, $mail->to);
            $this->assertEquals($this->emailAddress, $mail->to[0]['address']);
            $this->assertCount(0, $mail->cc);
            $this->assertCount(0, $mail->bcc);

            return true;
        });
    }

    /** @test */
    public function it_is_queued_with_connection_and_stack()
    {
        $exitCode = Artisan::call($this->command,[
            '--stack' => 'emails',
            '--connection' => 'sync'
        ]);

        $this->assertStringContainsString($this->emailAddress, Artisan::output());
        $this->assertEquals(0, $exitCode);

        Mail::assertQueued(TestMailable::class, function (TestMailable $mail) {
            $mail->build();
            $this->assertEquals('emails', $mail->queue);
            $this->assertEquals('sync', $mail->connection);
            $this->assertCount(1, $mail->to);
            $this->assertEquals($this->emailAddress, $mail->to[0]['address']);
            $this->assertCount(0, $mail->cc);
            $this->assertCount(0, $mail->bcc);

            return true;
        });
    }

    /** @test */
    public function it_is_queued_with__queue_flag_connection_and_stack()
    {
        $exitCode = Artisan::call($this->command,[
            '--queue' => null,
            '--stack' => 'emails',
            '--connection' => 'sync'
        ]);

        $this->assertStringContainsString($this->emailAddress, Artisan::output());
        $this->assertEquals(0, $exitCode);

        Mail::assertQueued(TestMailable::class, function (TestMailable $mail) {
            $mail->build();
            $this->assertEquals('emails', $mail->queue);
            $this->assertEquals('sync', $mail->connection);
            $this->assertCount(1, $mail->to);
            $this->assertEquals($this->emailAddress, $mail->to[0]['address']);
            $this->assertCount(0, $mail->cc);
            $this->assertCount(0, $mail->bcc);

            return true;
        });
    }

    /** @test */
    public function it_will_throw_an_exception_when_passing_an_invalid_mail_address()
    {
        $exitCode = Artisan::call($this->command, [
            'recipient' => 'notanemailaddress',
        ]);

        Mail::assertNotSent(TestMailable::class);

        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function it_will_throw_an_exception_if_it_cannot_find_an_email_address()
    {
        config(['mail.from.address' => null]);
        //$this->expectException(Exception::class);

        $exitCode = Artisan::call($this->command, [
            'recipient' => 'notanemailaddress',
        ]);

        Mail::assertNotQueued(TestMailable::class);

        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function it_can_be_sent_using_different_mail_drivers()
    {
        config(['mail.driver' => 'smtp']);

        $this->assertEquals('smtp', config('mail.driver'));
        $exitCode = Artisan::call($this->command);

        $this->assertStringContainsString('smtp', Artisan::output());
        $this->assertEquals(0, $exitCode);

        $exitCode = Artisan::call($this->command, ['--driver' => 'log']);
        $this->assertStringContainsString('log', Artisan::output());
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function the_driver_option_is_optional_but_cannot_be_blank()
    {
        config(['mail.driver' => 'smtp']);

        $this->assertEquals('smtp', config('mail.driver'));
        $exitCode = Artisan::call($this->command, ['--driver'=> '']);

        $this->assertStringContainsString('The driver field is required.', Artisan::output());

        Mail::assertNotSent(TestMailable::class);

        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function input_validation_shows_multiple_errors()
    {
        config(['mail.driver' => 'smtp']);

        $this->assertEquals('smtp', config('mail.driver'));

        $exitCode = Artisan::call($this->command, [
                'recipient' => 'notanemailaddress',
                '--driver'=> '',
            ]
        );

        $output = Artisan::output();

        $this->assertStringContainsString('The email must be a valid email address.', $output);
        $this->assertStringContainsString('The driver field is required.', $output);

        Mail::assertNotSent(TestMailable::class);

        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function it_can_use_full_presets_from_config()
    {
        config(['mail-test.presets.test' =>
            [
                'recipient' => 'preset@example.com',
                'driver' => 'presetDriver',
                'connection' => 'presetConnection',
                'stack' => 'presetStack'
            ]
        ]);

        $exitCode = Artisan::call($this->command, [
                '--preset' => 'test',
            ]
        );

        $output = Artisan::output();

        Mail::assertQueued(TestMailable::class, function (TestMailable $mail) {
            $mail->build();
            $this->assertEquals('presetStack', $mail->queue);
            $this->assertEquals('presetConnection', $mail->connection);
            $this->assertCount(1, $mail->to);
            $this->assertEquals('preset@example.com', $mail->to[0]['address']);
            $this->assertCount(0, $mail->cc);
            $this->assertCount(0, $mail->bcc);

            return true;
        });

        $this->assertStringContainsString('presetDriver', $output);
    }

    /** @test */
    public function it_can_use_partial_presets_from_config()
    {
        config(['mail-test.presets.test' =>
            [
                'recipient' => 'preset@example.com',
            ]
        ]);

        $exitCode = Artisan::call($this->command, [
                '--preset' => 'test',
            ]
        );

        $output = Artisan::output();

        Mail::assertSent(TestMailable::class, function (TestMailable $mail) {
            $mail->build();
            $this->assertCount(1, $mail->to);
            $this->assertEquals('preset@example.com', $mail->to[0]['address']);
            $this->assertCount(0, $mail->cc);
            $this->assertCount(0, $mail->bcc);

            return true;
        });
    }

    /** @test */
    public function a_preset_can_be_sent_via_queues()
    {
        config(['mail-test.presets.test' =>
            [
                'recipient' => 'preset@example.com',
                'queue' => true,
            ]
        ]);

        $exitCode = Artisan::call($this->command, [
                '--preset' => 'test',
            ]
        );

        $output = Artisan::output();

        Mail::assertQueued(TestMailable::class, function (TestMailable $mail) {
            $mail->build();
            $this->assertCount(1, $mail->to);
            $this->assertEquals('preset@example.com', $mail->to[0]['address']);
            $this->assertCount(0, $mail->cc);
            $this->assertCount(0, $mail->bcc);

            return true;
        });
    }

    /** @test */
    public function it_will_error_if_a_preset_does_not_exists()
    {
        $exitCode = Artisan::call($this->command, [
                '--preset' => 'test',
            ]
        );

        $output = Artisan::output();

        Mail::assertNotSent(TestMailable::class);

        $this->assertStringContainsString('The selected preset is invalid', $output);

        $this->assertEquals(1, $exitCode);
    }
}
