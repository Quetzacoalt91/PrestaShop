workflow "Code Quality" {
  on = "push"
  resolves = [
    "PHP-CS-Fixer",
  ]
}

action "PHP-CS-Fixer" {
  uses = "docker://oskarstark/php-cs-fixer-ga"
  secrets = ["GITHUB_TOKEN"]
  args = "--config=.php_cs.dist --diff --dry-run"
}
