import logging


def setup_logger(file_path, verbosity="DEBUG"):
    # set up logging to file - see previous section for more details

    logger = logging.getLogger()
    file_name = file_path.split('/')[-1].split('.')[0]

    logging.basicConfig(level=logging.getLevelName(verbosity),
                        format='%(asctime)s.%(msecs)03d [%(levelname)s]: %(message)s',
                        datefmt='%m-%d %H:%M:%S')
    # TODO add file handler etc later.
                        # filename=file_name+'.log')

    # quick adapter workaround for lack of handling pexpect debug info.
    logger.write = lambda text: logger.debug(text)
    logger.flush = flush


    logger.info("*"*30)
    logger.info("Test: " + file_name)
    logger.info("*"*30)
    logger.info("Test Setup")

    return logger

def flush():
    pass