#/bin/bash

function main() {
    # Check for git
    which git >> /dev/null
    if [[ $? != 0 ]]; then
        echo "Sorry, you need git for this.";
        exit 1;
    fi
    
    # Check for composer
    which composer >> /dev/null
    if [[ $? != 0 ]]; then
        echo "Sorry, you need composer for this.";
        exit 1;
    fi

    which phpcs >> /dev/null
    if [[ $? != 0 ]]; then
        echo "You need phpcs! let me grab it for you."
        composer global require squizlabs/php_codesniffer
    fi

    original_dir=$PWD
    phpcs_path=$(which phpcs)
    try_install
}

function try_install() {
    echo "phpcs found in $phpcs_path";
    read -p "Would you like to install to this directory? " -n 1 -r
    if [[ "$REPLY" =~ ^[Yy]$ ]]; then
        install_func
    else
        echo "\nAborting!"
    fi 
}

function install_func() {
    echo "\n\n------------ Updating ------------\n";
    install_dir="$(dirname $phpcs_path)"
    echo "Changing directory to $install_dir"
    cd $install_dir
    echo "Getting real phpcs install path"
    real_install="$(dirname $(readlink phpcs))"
    echo "Changing directory to $real_install"
    cd $real_install
    echo "Going up a directory"
    cd "../"
    echo "Changing directory to src/Standards"
    cd "src/Standards"
    local standards="$(ls -1)"

    # Slevomat
    if [[ $standards = *"SlevomatCodingStandard"* ]]; then
        rm -rf SlevomatCodingStandard
    fi

    echo "SlevomatCodingStandard: \033[33mUpdating\033[0m"
    git clone git@github.com:slevomat/coding-standard.git
    mv coding-standard/SlevomatCodingStandard SlevomatCodingStandard
    rm -rf coding-standard
    echo "Copied SlevomatCodingStandard from repo."

    # Hostnet
    if [[ $standards = *"Hostnet"* ]]; then
        rm -rf Hostnet
    fi

    echo "Hostnet: \033[33mUpdating\033[0m"
    git clone git@github.com:hostnet/phpcs-tool.git
    mv phpcs-tool/src/Hostnet Hostnet
    rm -rf phpcs-tool
    echo "Copied Hostnet from repo."

    # VariableAnalysis
    if [[ $standards = *"VariableAnalysis"* ]]; then
        rm -rf VariableAnalysis
    fi

    echo "VariableAnalysis: \033[33mUpdating\033[0m"
    git clone git@github.com:sirbrillig/phpcs-variable-analysis.git
    mv phpcs-variable-analysis/VariableAnalysis VariableAnalysis
    rm -rf phpcs-variable-analysis
    echo "Copied VariableAnalysis from repo."

    # XpBar
    if [[ $standards = *"XpBar"* ]]; then
        rm -rf XpBar
    fi

    echo "XpBar: \033[33mUpdating\033[0m"
    cp -rf $original_dir/XpBar XpBar 
    echo "Copied XpBar from repo."

    # Set Standard
    phpcs --config-set default_standard XpBar

    echo "\033[32m                                                
  mmmm  m    m   mmm    mmm  mmmmmm  mmmm   mmmm 
 #\"   \" #    # m\"   \" m\"   \" #      #\"   \" #\"   \"
 \"#mmm  #    # #      #      #mmmmm \"#mmm  \"#mmm 
     \"# #    # #      #      #          \"#     \"#
 \"mmm#\" \"mmmm\"  \"mmm\"  \"mmm\" #mmmmm \"mmm#\" \"mmm#\"
        \033[0m"
}

main
