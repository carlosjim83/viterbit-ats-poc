<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Persistence\DataFixtures;

use App\Application\Infrastructure\Persistence\DoctrineJobApplication;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ApplicationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $fixtures = [
            [
                'fullName' => 'Ada Lovelace',
                'email' => 'ada@example.com',
                'phone' => '+441111111111',
                'position' => 'Engineering Manager',
                'notes' => 'Remote only',
                'cvText' => 'Pioneer of computer science with extensive analytical experience.',
                'status' => 'received',
                'appliedAt' => new \DateTimeImmutable('-2 days'),
            ],
            [
                'fullName' => 'Grace Hopper',
                'email' => 'grace@example.com',
                'phone' => '+442222222222',
                'position' => 'Senior Developer',
                'notes' => 'Open to relocation',
                'cvText' => 'Expert in COBOL and compiler design. Led multiple engineering teams.',
                'status' => 'enriching',
                'appliedAt' => new \DateTimeImmutable('-1 day'),
            ],
            [
                'fullName' => 'Alan Turing',
                'email' => 'alan@example.com',
                'phone' => '+443333333333',
                'position' => 'Machine Learning Engineer',
                'notes' => '',
                'cvText' => 'Foundational work in computation and artificial intelligence.',
                'status' => 'enriched',
                'appliedAt' => new \DateTimeImmutable('-3 days'),
            ],
            [
                'fullName' => 'Margaret Hamilton',
                'email' => 'margaret@example.com',
                'phone' => '+444444444444',
                'position' => 'Engineering Manager',
                'notes' => 'Prefers small teams',
                'cvText' => 'Led the Apollo flight software team. Expert in systems engineering.',
                'status' => 'received',
                'appliedAt' => new \DateTimeImmutable('-5 hours'),
            ],
            [
                'fullName' => 'Donald Knuth',
                'email' => 'donald@example.com',
                'phone' => '+445555555555',
                'position' => 'Senior Developer',
                'notes' => 'Writing a book',
                'cvText' => 'Author of The Art of Computer Programming. Deep expertise in algorithms.',
                'status' => 'enriching',
                'appliedAt' => new \DateTimeImmutable('-1 hour'),
            ],
            [
                'fullName' => 'Barbara Liskov',
                'email' => 'barbara@example.com',
                'phone' => '+446666666666',
                'position' => 'Principal Engineer',
                'notes' => '',
                'cvText' => 'Pioneer of object-oriented programming and type theory.',
                'status' => 'enriched',
                'appliedAt' => new \DateTimeImmutable('-4 days'),
            ],
            [
                'fullName' => 'Tim Berners-Lee',
                'email' => 'tim@example.com',
                'phone' => '+447777777777',
                'position' => 'Machine Learning Engineer',
                'notes' => 'Interested in semantic web',
                'cvText' => 'Inventor of the World Wide Web. Strong background in distributed systems.',
                'status' => 'received',
                'appliedAt' => new \DateTimeImmutable('-30 minutes'),
            ],
            [
                'fullName' => 'Linus Torvalds',
                'email' => 'linus@example.com',
                'phone' => '+448888888888',
                'position' => 'Principal Engineer',
                'notes' => 'Kernel development',
                'cvText' => 'Creator of Linux kernel and Git. Expert in low-level systems programming.',
                'status' => 'enriched',
                'appliedAt' => new \DateTimeImmutable('-6 days'),
            ],
        ];

        foreach ($fixtures as $data) {
            $entity = new DoctrineJobApplication(
                (string) \Symfony\Component\Uid\Uuid::v4(),
                $data['fullName'],
                $data['email'],
                $data['phone'],
                $data['position'],
                $data['notes'],
                $data['cvText'],
                $data['status'],
                $data['appliedAt'],
            );
            $manager->persist($entity);
        }

        $manager->flush();
    }
}
