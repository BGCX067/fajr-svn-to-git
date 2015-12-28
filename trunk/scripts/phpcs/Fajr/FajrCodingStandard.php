<?php
if (class_exists('PHP_CodeSniffer_Standards_CodingStandard', true) === false) {
  throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_CodingStandard not found');
}

class PHP_CodeSniffer_Standards_Fajr_FajrCodingStandard extends PHP_CodeSniffer_Standards_CodingStandard
{

  public function getIncludedSniffs()
  {
    return array(
        'Generic/Sniffs/ControlStructures/InlineControlStructureSniff.php',
        'Generic/Sniffs/Formatting/DisallowMultipleStatementsSniff.php',
        'Generic/Sniffs/Formatting/SpaceAfterCastSniff.php',
        'Generic/Sniffs/Functions/OpeningFunctionBraceBsdAllmanSniff.php',
        'Generic/Sniffs/Metrics/CyclomaticComplexitySniff.php',
        'Generic/Sniffs/Metrics/NestingLevelSniff.php',
        'Generic/Sniffs/NamingConventions/UpperCaseConstantNameSniff.php',
        'Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php',
        'Generic/Sniffs/PHP/ForbiddenFunctionsSniff.php',
        'Generic/Sniffs/PHP/LowerCaseConstantSniff.php',
        'Generic/Sniffs/PHP/NoSilencedErrorsSniff.php',
        'Generic/Sniffs/WhiteSpace/DisallowTabIndentSniff.php',
        'Generic/Sniffs/Files/LineEndingsSniff.php',
        'PEAR',
        'Squiz/Sniffs/Functions/GlobalFunctionSniff.php',
        );

  }//end getIncludedSniffs()

  public function getExcludedSniffs()
  {
    return array(
        'Generic/Sniffs/Files/LineLengthSniff.php',
        'Generic/Sniffs/WhiteSpace/ScopeIndentSniff.php',
        'PEAR/Sniffs/Commenting/FileCommentSniff.php',
        'PEAR/Sniffs/Commenting/ClassCommentSniff.php',
        'PEAR/Sniffs/NamingConventions/ValidFunctionNameSniff.php',
        'PEAR/Sniffs/NamingConventions/ValidVariableNameSniff.php',
        'PEAR/Sniffs/WhiteSpace/ScopeIndentSniff.php',
        'PEAR/Sniffs/Files/LineLengthSniff.php',
        );
  }


}//end class
