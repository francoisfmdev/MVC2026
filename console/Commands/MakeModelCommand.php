<?php

declare(strict_types=1);

namespace Console\Commands;

use Core\Console\Command;

final class MakeModelCommand extends Command
{
    public function handle(array $arguments): int
    {
        $name = $this->positional($arguments)[0] ?? null;
        if ($name === null) {
            $this->error('Usage : php framework make:model <Nom> [--migration]');

            return 1;
        }

        $class = $this->studly($name);
        $table = $this->plural($this->snake($class));

        $contents = $this->stub('model', [
            '{{class}}' => $class,
            '{{table}}' => $table,
        ]);
        $this->write(BASE_PATH . '/app/Models/' . $class . '.php', $contents);

        if ($this->option($arguments, 'migration') !== null) {
            $migrate = new MakeMigrationCommand();
            $migrate->handle(['create_' . $table . '_table']);
        }

        return 0;
    }
}
