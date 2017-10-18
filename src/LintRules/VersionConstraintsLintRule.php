<?php
namespace SLLH\ComposerLint;

class VersionConstraintsLintRule implements LintRule {
    
    /*
     * {@inheritdoc }
     */
    public function __construct(Array $config) {
    }


    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {
        $linksSections = array('require', 'require-dev', 'conflict', 'replace', 'provide', 'suggest');

        foreach ($linksSections as $linksSection) {
            if (array_key_exists($linksSection, $manifest)) {
                $errors = array_merge($errors, $this->validateVersionConstraints($manifest[$linksSection]));
            }
        }

        return $errors;
    }

    /**
     * @param string[] $packages
     *
     * @return array
     */
    private function validateVersionConstraints(array $packages)
    {
        $errors = array();

        foreach ($packages as $name => $constraint) {
            // Checks if OR format is correct
            // From Composer\Semver\VersionParser::parseConstraints
            $orConstraints = preg_split('{\s*\|\|?\s*}', trim($constraint));
            foreach ($orConstraints as &$subConstraint) {
                // Checks ~ usage
                $subConstraint = str_replace('~', '^', $subConstraint);

                // Checks for usage like ^2.1,>=2.1.5. Should be ^2.1.5.
                // From Composer\Semver\VersionParser::parseConstraints
                $andConstraints = preg_split('{(?<!^|as|[=>< ,]) *(?<!-)[, ](?!-) *(?!,|as|$)}', $subConstraint);
                if (2 === count($andConstraints) && '>=' === substr($andConstraints[1], 0, 2)) {
                    $andConstraints[1] = '^'.substr($andConstraints[1], 2);
                    array_shift($andConstraints);
                    $subConstraint = implode(',', $andConstraints);
                }
            }

            $expectedConstraint = implode(' || ', $orConstraints);

            if ($expectedConstraint !== $constraint) {
                array_push($errors, sprintf(
                    "Requirement format of '%s:%s' is not valid. Should be '%s'.",
                    $name,
                    $constraint,
                    $expectedConstraint
                ));
            }
        }

        return $errors;
    }

}

