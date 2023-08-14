<?php

namespace Insitaction\FieldEncryptBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Insitaction\FieldEncryptBundle\Doctrine\DBAL\Types\EncryptedString;
use Insitaction\FieldEncryptBundle\EventListener\EncryptionListener;
use Insitaction\FieldEncryptBundle\Service\EncryptService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'migrate:user-password:encryption', description: 'Archivate old news.')]
final class MigrationCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EncryptService $encryptService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'entities',
                InputArgument::IS_ARRAY,
                'Please indicate the entities to be migrated ? separate multiple names with a space (ex: App\\Entity\\User App\\Entity\\Admin)'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var array<int, class-string> $tomigrate */
        $tomigrate = $input->getArgument('entities');

        foreach ($tomigrate as $classToMigrate) {
            $metadata = $this->em->getMetadataFactory()->getMetadataFor($classToMigrate);
            $table = $metadata->table['name'];
            $conn = $this->em->getConnection();
            $result = $conn->prepare('SELECT * FROM ' . $table)->executeQuery();

            foreach ($result->fetchAllAssociative() as $element) {
                foreach ($metadata->fieldMappings as $field) {
                    $value = $element[$field['columnName']];

                    if (!is_string($value)) {
                        continue;
                    }

                    if (EncryptedString::NAME === $field['type'] && !str_ends_with($value, EncryptionListener::ENCRYPTION_MARKER)) {
                        $newValue = $this->encryptService->encrypt($this->encryptService->decrypt($value)) . EncryptionListener::ENCRYPTION_MARKER;
                        $conn->executeQuery('UPDATE `' . $table . '` SET `' . $field['columnName'] . '` = "' . addslashes($newValue) . '" WHERE `id` = ' . $element['id']);
                        $io->info('Reencrypt table:' . $table . ' column:' . $field['columnName'] . ' id:' . $element['id']);
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
