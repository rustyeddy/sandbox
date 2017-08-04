import logging


class Component(object):

    """
    This is the tempalte class for components
    """

    def __init__(self, logger):
        self.default = ''
        self.wrapped = sys.modules[__name__]
        self.count = 0
        self.logger = logger
    #
    # def __getattr__(self, name):
    #     """
    #      This will invoke, if the attribute wasn't found the usual ways.
    #      Here it will look for assert_attribute and will execute when
    #      AttributeError occurs.
    #      It will return the result of the assert_attribute.
    #     """
    #     try:
    #         return getattr(self.wrapped, name)
    #     except AttributeError as error:
    #         # NOTE: The first time we load a driver module we get this error
    #         if "'module' object has no attribute '__path__'" in error:
    #             pass
    #         else:
    #             self.logger.error(str(error.__class__) + " " + str(error))
    #         try:
    #             def experimentHandling(*args, **kwargs):
    #                 if main.EXPERIMENTAL_MODE:
    #                     result = self.experimentRun(*args, **kwargs)
    #                     self.logger.info("EXPERIMENTAL MODE. API " +
    #                                      str(name) +
    #                                      " not yet implemented. " +
    #                                      "Returning dummy values")
    #                     return result
    #                 else:
    #                     return False
    #             return experimentHandling
    #         except TypeError as e:
    #             self.logger.error("Arguments for experimental mode does not" +
    #                               " have key 'retruns'" + e)


    def execute(self, cmd):
        return True
        # import commands
        # return commands.getoutput( cmd )

    def disconnect(self):
        return True

    def cleanup(self):
        return True

if __name__ != "__main__":
    import sys
    # sys.modules[ __name__ ] = Component()
