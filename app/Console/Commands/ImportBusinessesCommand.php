<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use League\Csv\Reader;
use League\Tactician\CommandBus;
use TheRestartProject\RepairDirectory\Application\Commands\Business\ImportFromCsvRow\ImportFromCsvRowCommand;

class ImportBusinessesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restart:import:businesses {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import businesses from a CSV file';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(CommandBus $commandBus)
    {
        // load the CSV document from a file path
        $file = $this->argument('file');
        $this->output->writeln('Reading ' . $file);
        $csv = Reader::createFromPath($file);
        $rows = $csv->fetchAssoc();
        foreach($rows as $row) {
            $commandBus->handle(new ImportFromCsvRowCommand($row));
        }
        $this->output->writeln('Complete');
    }
}
