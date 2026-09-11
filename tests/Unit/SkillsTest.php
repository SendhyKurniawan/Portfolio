<?php

use PHPUnit\Framework\TestCase;

final class SkillsTest extends TestCase
{
    /** @param list<string> $stacks */
    private function projects(array $stacks): array
    {
        return array_map(static fn(string $stack) => ['tech_stack' => $stack], $stacks);
    }

    public function testSplitsCommaSeparatedStacks(): void
    {
        $skills = collect_skills($this->projects(['HTML, CSS, PHP']), []);

        $this->assertSame(['CSS', 'HTML', 'PHP'], $skills);
    }

    public function testTrimsSurroundingWhitespace(): void
    {
        $this->assertSame(['Bootstrap'], collect_skills($this->projects(['   Bootstrap   ']), []));
    }

    public function testDropsEmptyEntries(): void
    {
        $this->assertSame(['PHP'], collect_skills($this->projects([',, PHP , ,']), []));
    }

    public function testDeduplicatesRegardlessOfCasingAndKeepsTheFirstSpelling(): void
    {
        $skills = collect_skills($this->projects(['MySQL, php', 'mysql', 'PHP']), []);

        $this->assertSame(['MySQL', 'php'], $skills);
    }

    public function testExtraSkillsJoinTheList(): void
    {
        $skills = collect_skills($this->projects(['PHP']), ['Docker', 'Git']);

        $this->assertSame(['Docker', 'Git', 'PHP'], $skills);
    }

    public function testSortsAlphabeticallyIgnoringCase(): void
    {
        $this->assertSame(['apple', 'Banana', 'cherry'], collect_skills($this->projects(['cherry, apple, Banana']), []));
    }

    public function testProjectsWithoutATechStackAreSkipped(): void
    {
        $this->assertSame(['Docker'], collect_skills([['title' => 'No stack here'], ['tech_stack' => null]], ['Docker']));
    }

    public function testTwoNamesForTheSameThingCountOnce(): void
    {
        $skills = collect_skills($this->projects(['JS, Bootstrap 5', 'JavaScript, Bootstrap']), []);

        $this->assertSame(['Bootstrap', 'JavaScript'], $skills);
    }

    public function testNoProjectsLeavesOnlyTheExtras(): void
    {
        $this->assertSame(['Docker'], collect_skills([], ['Docker']));
    }
}
