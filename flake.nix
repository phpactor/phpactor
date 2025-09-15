# this is a WIP flake for development and experimentation only
{
  description = "phpactor/phpactor";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
  };

  outputs = inputs @ {
    self,
    flake-parts,
    ...
  }:
    flake-parts.lib.mkFlake {inherit inputs;} {
      # This flake is for Linux (x86) and Apple (darwin) systems
      # If you need more systems, inspect `nixpkgs.lib.systems.flakeExposed` and
      # add them to this list.
      #
      # $ nix repl "<nixpkgs>"
      # nix-repl> lib.systems.flakeExposed
      systems = ["x86_64-linux" "aarch64-linux"];

      perSystem = {
        pkgs,
        system,
        ...
      }: let 
        jaeger = pkgs.stdenv.mkDerivation {
          pname = "jaeger";
          version = "1.49.0";
          src = pkgs.fetchurl {
            url = "https://github.com/jaegertracing/jaeger/releases/download/v1.73.0/jaeger-2.10.0-linux-amd64.tar.gz";
            hash = "sha256-/hqgg1MNAqlBWOUJSjM0NikYLKye6GQAM3KinfHAUNM=";
          };
          phases = [ "unpackPhase" "installPhase" "fixupPhase" ];
          installPhase = ''
            mkdir -p $out/bin
            install ./jaeger $out/bin
          '';
        };
        phpWithXdebug = (pkgs.php84.buildEnv {
          extensions = ({ enabled, all }: enabled ++ (with all; [
            xdebug
            opentelemetry
          ]));
          extraConfig = ''
            xdebug.mode=debug
          '';
        });
      in {
        # Run `nix fmt` to reformat the nix files
        formatter = pkgs.alejandra;

        # Run `nix develop` to enter the development shell
        devShells.default = pkgs.mkShellNoCC {
          name = "php-devshell";

          buildInputs = [
            jaeger
            pkgs.python3
            phpWithXdebug
            pkgs.php84.packages.composer

          ];
          shellHook = ''
          if [ ! -d ".venv" ]; then
            python3 -m venv .venv;
          fi
          source .venv/bin/activate;'';
        };
      };
    };
}
