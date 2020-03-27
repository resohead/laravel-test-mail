<?php

namespace Resohead\LaravelTestMail;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Validator;
use Resohead\LaravelTestMail\TestMailable;
use Resohead\LaravelTestMail\SendTestEmailJob;
use \Illuminate\Contracts\Config\Repository as Config;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {recipient?} {--queue} {--driver=} {--connection=} {--stack=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email';

    protected $validator;
    protected $config;

    protected $recipient;
    protected $driver;
    protected $stack;
    protected $connection;

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
        $this->getArgumentsWithDefaults();
        
        $validation = $this->validator::make([
                'email' => $this->recipient,
                'driver' => $this->driver
            ], $this->rules()
        );

        if ($validation->fails()) {
            collect($validation->errors()->all())->each(function($error){
                $this->error($error);
            });
            return 1;
        }
        
        $this->config->set('mail.driver', $this->driver);
        
        $mailable = new TestMailable($this->recipient);

        $this->isOnQueue()
            ? Mail::queue($mailable->onConnection($this->connection)->onQueue($this->stack)) 
            : Mail::send($mailable);
            
        $this->comment("A test email ($this->driver) has been sent to $this->recipient");
    }

    protected function getArgumentsWithDefaults()
    {
        $this->recipient = $this->argument('recipient') ?? $this->config->get('mail.from.address');
        $this->driver = $this->option('driver') ?? $this->config->get('mail.driver');
        $this->connection = $this->option('connection') ?? $this->config->get('queue.default');
        $this->stack = $this->option('stack') ?: 'default';
    }

    protected function isOnQueue(): bool
    {
        return $this->option('queue') || $this->option('stack') || $this->option('connection');
    }

    protected function rules(): array
    {
        return [
            'email' => 'email',
            'driver' => 'required'
        ];
    }
}
