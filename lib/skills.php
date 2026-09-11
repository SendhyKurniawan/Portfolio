<?php
// The skills list on the "System Properties" window is derived from the projects themselves,
// so installing a program with a new stack also teaches the site a new skill.

// What this site is built with; the projects don't list it anywhere
const SITE_SKILLS = ['PHP', 'MySQL', 'Docker', 'JavaScript', 'Bootstrap'];

// Projects spell some stacks differently; the skills list shows one name per skill
const SKILL_ALIASES = [
    'js' => 'JavaScript',
    'bootstrap 5' => 'Bootstrap',
    'bootstrap5' => 'Bootstrap',
];

/**
 * Every distinct technology used across the projects, plus the extras, sorted A-Z.
 *
 * @param array<int, array<string, mixed>> $projects rows with a comma-separated `tech_stack`
 * @param list<string> $extra
 * @return list<string>
 */
function collect_skills(array $projects, array $extra = SITE_SKILLS): array
{
    $names = $extra;
    foreach ($projects as $project) {
        $names = array_merge($names, explode(',', (string) ($project['tech_stack'] ?? '')));
    }

    $skills = [];
    foreach ($names as $name) {
        $name = SKILL_ALIASES[strtolower(trim($name))] ?? trim($name);
        // "MySQL" and "mysql" are the same skill; the first spelling seen wins
        if ($name !== '' && !isset($skills[strtolower($name)])) {
            $skills[strtolower($name)] = $name;
        }
    }

    uasort($skills, 'strcasecmp');

    return array_values($skills);
}
