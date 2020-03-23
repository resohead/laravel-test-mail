<?php

namespace Resohead\LaravelTestMail;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Resohead\LaravelTestMail\TestMail;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Validator;
use Resohead\LaravelTestMail\SendTestEmailJob;
use \Illuminate\Contracts\Config\Repository as Config;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {recipient?} {--driver=} {--queue} {--stack=} {--connection=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email';

    protected $validator;
    protected $config;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Validator $validator, Config $config)
    {
        parent::__construct();
        $this->validator = $validator;
        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $recipient = $this->argument('recipient') ?? config('mail.from.address');
        $driver = $this->option('driver') ?? $this->config->get('mail.driver');
        $stack = $this->option('stack') ?: 'default';
        $connection = $this->option('connection') ?? $this->config->get('queue.default');
        
        $validation = $this->validator::make([
                'email' => $recipient,
                'driver' => $driver
            ], $this->rules()
        );

        if ($validation->fails()) {
            collect($validation->errors()->all())->each(function($error){
                $this->error($error);
            });
            return 1;
        }
        
        $this->config->set('mail.driver', $driver);
        
        $mailable = new TestMail($recipient);

        (bool) $this->option('queue') || ($this->option('stack') || $this->option('connection'))
            ? Mail::queue($mailable->onConnection($connection)->onQueue($stack)) 
            : Mail::send($mailable);
        
        $this->comment("A test email (${driver}) has been sent to ${recipient}");
    }

    protected function rules()
    {
        return [
            'email' => 'email',
            'driver' => 'required'
        ];
    }
}
