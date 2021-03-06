export declare function log(str: string): void;
export declare function warn(str: string): void;
export declare function error(str: string): void;
export declare function deprecateLog(str: string): void;
export declare function deprecateReplaceLog(oldOpt: string, newOpt: string, scope?: string): void;
export declare function consoleLog(...args: unknown[]): void;
/**
 * If in __DEV__ environment, get console printable message for users hint.
 * Parameters are separated by ' '.
 * @usuage
 * makePrintable('This is an error on', someVar, someObj);
 *
 * @param hintInfo anything about the current execution context to hint users.
 * @throws Error
 */
export declare function makePrintable(...hintInfo: unknown[]): string;
/**
 * @throws Error
 */
export declare function throwError(msg?: string): void;
