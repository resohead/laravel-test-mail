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
    protected $signature = 'mail:test {recipient?} {--preset=} {--queue} {--driver=} {--connection=} {--stack=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email';

    protected $validator;
    protected $config;

    protected $preset;
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
        $this->setArgumentDefaults();

        $validation = $this->validator::make([
                'email' => $this->recipient,
                'driver' => $this->driver,
                'preset' => $this->preset
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

    protected function setArgumentDefaults(): void
    {
        $this->setPreset();
        $this->setRecipient();
        $this->setDriver();
        $this->setConnection();
        $this->setStack();
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getPresetValue($key, $default = null)
    {
        return $this->config->get(
                implode('.', [$this->getConfigPath(), $this->preset, $key]), 
                $default
            );
    }

    protected function getConfigPath(): string
    {
        return 'mail-test.presets';
    }

    protected function getConfigKeys(): array
    {
        return array_keys(
            $this->config->get($this->getConfigPath())
        );
    }

    protected function setPreset(): void
    {
        $this->preset = $this->option('preset');
    }

    protected function setRecipient(): void
    {
        $this->recipient = $this->argument('recipient') ??
             $this->getPresetValue('recipient', $this->config->get('mail.from.address'));
    }

    protected function setDriver(): void
    {
        $this->driver = $this->option('driver') ?? 
            $this->getPresetValue('driver', $this->config->get('mail.driver'));
    }

    protected function setConnection(): void
    {
        $this->connection = $this->option('connection') ?? 
            $this->getPresetValue('connection', $this->config->get('queue.default'));
    }

    protected function setStack(): void
    {
        $this->stack = $this->option('stack') ?: $this->getPresetValue('stack', 'default');
    }

    protected function isOnQueue(): bool
    {
        return $this->hasQueueOptions() || $this->hasQueuePresets();
    }

    protected function hasQueueOptions(): bool
    {
        return $this->option('queue') || $this->option('stack') || $this->option('connection');
    }

    protected function hasQueuePresets(): bool
    {
        return $this->getPresetValue('queue') || $this->getPresetValue('connection') || $this->getPresetValue('stack');
    }

    protected function rules(): array
    {
        return [
            'email' => 'email',
            'driver' => 'required',
            'preset' => 'nullable|sometimes|in:'.implode(',', $this->getConfigKeys()),
        ];
    }
}
